<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

class Facturas extends \REST_Controller
{
    private $codigoPuntoVenta;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('facturasv_model');
        $this->load->library('facturasv/Uuid');
        $this->load->library('facturasv/NumeroALetras');
        $this->codigoPuntoVenta = "PV01";
    }

    public function index_get()
    {
        echo "test";
        exit;
        $res = array();
        $dteJSON = array();
        $resFacturas = array();
        $facturas = $this->facturasv_model->get_facturas_listas_para_mh();

        $formatter = new NumeroALetras();

        if (count($facturas) > 0) {
            foreach ($facturas as $key => $value) {

                $fechaFactEmi = explode(" ", $value['datecreated']);

                $tipoDocumento = "";
                $codigoPuntoVenta = "PV01";
                $factDatosGenerales = $this->facturasv_model->get_informacion_general($codigoPuntoVenta);

                if ($value['prefix'] == 'FCF-') {
                    $tipoDocumento = "01";
                }

                $factCorrelativo = $this->facturasv_model->get_correlativo_por_tipo_documento($tipoDocumento);

                //recuperar detalle de la factura.
                $facturaDetalle = $this->facturasv_model->get_detalle_factura_pagada($value['id']);


                $factCliente = $this->facturasv_model->get_cliente_por_factura_id($value['id']);

                $numItem = count($facturaDetalle);

                $detalleCuerpoDoc = array();
                foreach ($facturaDetalle as $keyDetalle => $valueItem) {
                    $detalleCuerpoDoc [] = [
                        "numItem" => $this->convertIntegerValue($valueItem['item_order']),
                        "tipoItem" => 2,
                        "numeroDocumento" => null,
                        "cantidad" => $this->convertIntegerValue($valueItem['qty']),
                        "codigo" => null,
                        "codTributo" => null,
                        "uniMedida" => 99,
                        "descripcion" => $valueItem['description'],
                        "precioUni" => $this->convertToFloatValue($valueItem['rate']),
                        "montoDescu" => 0.00,
                        "ventaNoSuj" => 0.00,
                        "ventaExenta" => 0.00,
                        "ventaGravada" => $this->convertToFloatValue($valueItem['rate']),
                        "tributos" => null,
                        "psv" => 0.0,
                        "noGravado" => 0.0,
                        "ivaItem" => $this->convertToFloatValue($this->convertToFloatValue($valueItem['rate']) * 0.13) * $this->convertToFloatValue($valueItem['qty'])
                    ];
                }


                $dteJSON = [
                    "identificacion" => [
                        "version" => 1,
                        "ambiente" => $factDatosGenerales->ambiente,
                        "tipoDte" => $tipoDocumento,
                        "numeroControl" => $factCorrelativo->corre,
                        "codigoGeneracion" => strtoupper($this->uuid->v4()),
                        "tipoModelo" => 1,
                        "tipoOperacion" => 1,
                        "tipoContingencia" => null,
                        "motivoContin" => null,
                        "fecEmi" => $fechaFactEmi[0],
                        "horEmi" => $fechaFactEmi[1],
                        "tipoMoneda" => "USD"
                    ],
                    "documentoRelacionado" => null,
                    "emisor" => [
                        "nit" => str_replace("-", "", $factDatosGenerales->nit),
                        "nrc" => $factDatosGenerales->nrc,
                        "nombre" => $factDatosGenerales->nombreRazonSocial,
                        "codActividad" => $factDatosGenerales->codigoActividad,
                        "descActividad" => $factDatosGenerales->descripcionActividad,
                        "nombreComercial" => $factDatosGenerales->nombreComercial,
                        "tipoEstablecimiento" => $factDatosGenerales->codigoTipoEstablecimiento,
                        "direccion" => [
                            "departamento" => $factDatosGenerales->codigoDepartamento,
                            "municipio" => $factDatosGenerales->codigoMunicipio,
                            "complemento" => $factDatosGenerales->direccion
                        ],
                        "telefono" => $factDatosGenerales->telefono,
                        "correo" => $factDatosGenerales->correo,
                        "codEstableMH" => $factDatosGenerales->codigoMH,
                        "codEstable" => $factDatosGenerales->idSucursal . "0",
                        "codPuntoVentaMH" => $factDatosGenerales->codigoPuntoVentaMH . "0",
                        "codPuntoVenta" => $factDatosGenerales->codigoPuntoVenta
                    ],
                    "receptor" => [
                        "tipoDocumento" => "13",
                        "numDocumento" => $factCliente->documento,
                        "nrc" => null,
                        "nombre" => $factCliente->compania,
                        "codActividad" => "10001",
                        "descActividad" => "EMPLEADOS",
                        "direccion" => [
                            "departamento" => "02",
                            "municipio" => "05",
                            "complemento" => $factCliente->direccion],
                        "telefono" => $factCliente->telefono,
                        "correo" => $factCliente->mail
                    ],
                    "otrosDocumentos" => null,
                    "ventaTercero" => null,
                    "cuerpoDocumento" =>
                        $detalleCuerpoDoc,
                    "resumen" => [
                        "totalNoSuj" => 0.0,
                        "totalExenta" => 0.0,
                        "totalGravada" => 0.0,
                        "subTotalVentas" => $this->convertToFloatValue($value['subtotal']),
                        "descuNoSuj" => 0.0,
                        "descuExenta" => 0.0,
                        "descuGravada" => 0.0,
                        "porcentajeDescuento" => 0.0,
                        "totalDescu" => 0.0,
                        "tributos" => [],
                        "subTotal" => $this->convertToFloatValue($value['subtotal']),
                        "ivaRete1" => 0.0,
                        "reteRenta" => 0.0,
                        "montoTotalOperacion" => 0.0,
                        "totalNoGravado" => -0.0,
                        "totalPagar" => $this->convertToFloatValue($value['total']),
                        "totalLetras" => $formatter->toWords($value['total']),
                        "totalIva" => 0.0,
                        "saldoFavor" => 0.0,
                        "condicionOperacion" => 1,
                        "pagos" => null,
                        "numPagoElectronico" => null
                    ],
                    "extension" => null,
                    "apendice" => null
                ];
            }
        }


        $res['error'] = false;
        $res['message'] = 'success get data';
        $res['data'] = $dteJSON;

        $this->response($res, 200);
    }

    public function facturacodetomh_get($code_to_mh)
    {

        $res = array();
        $dteJSON = array();
        $datosControlFact = array();
        $resFacturas = array();
        $factura = $this->facturasv_model->get_facturas_listas_para_mh_by_code($code_to_mh);


        $clientGroup = 0;
        $estimateNumber = "";


        if (count($factura) > 0) {
            foreach ($factura as $key => $value) {

                $estimateNumber = $value['estimateNumber'];

                $fechaFactEmi = explode(" ", $value['datecreated']);

                $tipoDocumento = "";

                //agregar la lista de prefijos
                if ($value['prefix'] == 'FCF-') { //Factura
                    $tipoDocumento = "01";
                } else if ($value['prefix'] == 'CRE-') { //credito
                    $tipoDocumento = "03";
                } else if ($value['prefix'] == 'FEX-') {  //factura exportacion
                    $tipoDocumento = "11";
                } else if ($value['prefix'] == 'FSE-') { //factura sujero excluido
                    $tipoDocumento = "14";
                }

                //recuperar detalle de la factura.
                $facturaDetalle = $this->facturasv_model->get_detalle_factura_pagada($value['id']);

                $factCliente = $this->facturasv_model->get_cliente_por_factura_id($value['id']);


                $clientGroup = $factCliente->groupid;

                $numItem = count($facturaDetalle);

                list($sumatoriaIVA, $detalleCuerpoDoc, $sumatoriaVenta, $sumatoriaVentaExenta, $valorGranContribuyente) = $this->calcularDetalleFactura($facturaDetalle, $value['prefix'], $value['tipoVenta'], $clientGroup);

                //var_dump($sumatoriaIVA, $detalleCuerpoDoc, $sumatoriaVenta, $sumatoriaVentaExenta, $valorGranContribuyente);
                //exit;

                $dteJSON = $this->generarJSON($tipoDocumento, $factCliente, $fechaFactEmi, $value, $sumatoriaIVA, $detalleCuerpoDoc, $sumatoriaVenta, $sumatoriaVentaExenta, $valorGranContribuyente);

            }

            $datosControlFact['identicadorNumInterno'] = $factura[0]['id'];
            $datosControlFact['correlativoFactCRM'] = $factura[0]['number'];
            $datosControlFact['adminnote'] = $factura[0]['adminnote'];
            $datosControlFact['codigoGeneracion'] = $dteJSON['identificacion']['codigoGeneracion'];
            $datosControlFact['numeroControlMH'] = $dteJSON['identificacion']['numeroControl'];
            $datosControlFact['codigoTipoDTE'] = $dteJSON['identificacion']['tipoDte'];
            $datosControlFact['prefix'] = str_replace("-", "", $value['prefix']);
            $datosControlFact['version'] = $dteJSON['identificacion']['version'];
            $datosControlFact['fechaFactura'] = $factura[0]['datecreated'];
            $datosControlFact['codigoPuntoVenta'] = $this->codigoPuntoVenta;
            $datosControlFact['clienteGrupo'] = $clientGroup;
            $datosControlFact['numeroEstimacion'] = $estimateNumber;

        }


        $res['error'] = false;
        $res['message'] = 'success get data';
        $res['datainfo'] = $datosControlFact;
        $res['data'] = $dteJSON;

        $this->response($res, 200);

    }

    public function facturasmh_get($page, $limit)
    {

        $page = $page ?: 1;
        $limit = $limit ?: 10;
        $offset = ($page - 1) * $limit;

        $facturas = $this->facturasv_model->get_facturas_envio_mh($limit, $offset);

        $total = $this->facturasv_model->count_facturas_envio_mh();

        $totalPages = ceil($total / $limit);

        $res['error'] = false;
        $res['message'] = 'success get data';
        $res['num_pages'] = $totalPages;
        $res['currentPage'] = $page;
        $res['data'] = $facturas;


        $this->response($res, 200);
    }

    public function totalfacturasmh_get()
    {


        $res['error'] = false;
        $res['message'] = 'success get data';
        $res['data'] = $this->facturasv_model->count_facturas_envio_mh();

        $this->response($res, 200);

    }

    public function facturamh_put($factura)
    {


        $result = $this->facturasv_model->set_factura_sellada($factura);

        $res['error'] = false;
        $res['message'] = 'Cambio de estado a procesada con exito';
        $res['data'] = $result;

        return $this->response($res, 200);

    }

    public function convertIntegerValue($value)
    {
        return (int)$value;
    }

    public function convertToFloatValue($value)
    {
        return (float)number_format((float)$value, 2, '.', '');
    }

    function calcularDetalleFactura($detalleFactura, $tipoFactura, $tipoVenta, $clientGroup)
    {


        $sumatoria = 0.0;
        $sumatoriaVenta = 0.0;
        $sumatoriaVentaExenta = 0.0;
        $valorGranContribuyente = 0.0;

        $detalleCuerpoDoc = array();

        //var_dump($detalleFactura);
        //exit;

        if ($tipoFactura == 'FCF-') { // 01 - factura consumidor final

            foreach ($detalleFactura as $keyDetalle => $valueItem) {

                $calculoIVA = 0.0;
                $ventaGravada = 0.0;
                $ventaExenta = 0.0;
                $precioUnitarioGravado = 0.0;
                $precioUnitarioExento = 0.0;
                if ($tipoVenta == "GRAVADO") {
                    $calculoIVA = $this->convertToFloatValue(($valueItem['rate'] * $this->convertToFloatValue($valueItem['qty'])) * 0.13);
                    $ventaGravada = $this->convertToFloatValue(($valueItem['qty'] * $valueItem['rate'])) + $calculoIVA;
                    $precioUnitarioGravado = $this->convertToFloatValue($valueItem['rate'] * 1.13);
                } else if ($tipoVenta == "EXENTO") {
                    $ventaExenta = $this->convertToFloatValue($valueItem['qty'] * $valueItem['rate']);
                    $precioUnitarioExento = $this->convertToFloatValue($valueItem['rate']);
                }

                $detalleCuerpoDoc [] = [
                    "numItem" => $this->convertIntegerValue($valueItem['item_order']),
                    "tipoItem" => 2,
                    "numeroDocumento" => null,
                    "cantidad" => $this->convertToFloatValue($valueItem['qty']),
                    "codigo" => null,
                    "codTributo" => null,
                    "uniMedida" => $this->convertIntegerValue($valueItem['unit']),
                    "descripcion" => $valueItem['description'] . " (" . $valueItem['long_description'] . ")",
                    "precioUni" => ($precioUnitarioExento > 0) ? $precioUnitarioExento : $precioUnitarioGravado,
                    "montoDescu" => 0.00,
                    "ventaNoSuj" => 0.00,
                    "ventaExenta" => $ventaExenta,
                    "ventaGravada" => $ventaGravada,
                    "tributos" => null,
                    "psv" => 0.0,
                    "noGravado" => 0.0,
                    "ivaItem" => ($ventaExenta > 0) ? 0.0 : round($calculoIVA, 2)
                ];

                if ($tipoVenta == "GRAVADO") {
                    $sumatoria += $this->convertToFloatValue(($valueItem['rate'] * $this->convertToFloatValue($valueItem['qty'])) * 0.13);
                    $sumatoriaVenta += $this->convertToFloatValue(($valueItem['rate'] * $this->convertToFloatValue($valueItem['qty'])));
                } else if ($tipoVenta == "EXENTO") {
                    $sumatoriaVentaExenta += $this->convertToFloatValue(($valueItem['rate'] * $this->convertToFloatValue($valueItem['qty'])));
                }
            }

            if ($clientGroup == "8") {
                $valorGranContribuyente = $sumatoriaVenta * 0.01;
            }


        } else if ($tipoFactura == 'CRE-') { // 03

            foreach ($detalleFactura as $keyDetalle => $valueItem) {

                $tributo = null;
                $ventaGravada = 0.00;
                $ventaExenta = 0.0;
                if ($tipoVenta == "GRAVADO") {
                    $tributo = [
                        "20"
                    ];
                    $ventaGravada = $this->convertToFloatValue(($valueItem['qty'] * $valueItem['rate']));
                } else if ($tipoVenta == "EXENTO") {
                    $ventaExenta = $this->convertToFloatValue(($valueItem['qty'] * $valueItem['rate']));
                }

                $detalleCuerpoDoc [] = [
                    "numItem" => $this->convertIntegerValue($valueItem['item_order']),
                    "tipoItem" => 2,
                    "numeroDocumento" => null,
                    "cantidad" => $this->convertToFloatValue($valueItem['qty']),
                    "codigo" => null,
                    "codTributo" => null,
                    "uniMedida" => $this->convertIntegerValue($valueItem['unit']),
                    "descripcion" => $valueItem['description'] . " (" . $valueItem['long_description'] . ")",
                    "precioUni" => $this->convertToFloatValue($valueItem['rate']),
                    "montoDescu" => 0.00,
                    "ventaNoSuj" => 0.00,
                    "ventaExenta" => $ventaExenta,
                    "ventaGravada" => $ventaGravada,
                    "tributos" => $tributo,
                    "psv" => 0.0,
                    "noGravado" => 0.0,
                ];

                if ($tipoVenta == "GRAVADO") {
                    $sumatoria += $this->convertToFloatValue(($valueItem['rate'] * $this->convertToFloatValue($valueItem['qty'])) * 0.13);
                    $sumatoriaVenta += $this->convertToFloatValue(($valueItem['rate'] * $this->convertToFloatValue($valueItem['qty'])));
                } else if ($tipoVenta == "EXENTO") {
                    $sumatoriaVentaExenta += $this->convertToFloatValue(($valueItem['rate'] * $this->convertToFloatValue($valueItem['qty'])));
                }

            }

            if ($clientGroup == "8") {
                $valorGranContribuyente = $sumatoriaVenta * 0.01;
            }


        } else if ($tipoFactura == 'FEX-') {
            foreach ($detalleFactura as $keyDetalle => $valueItem) {
                $detalleCuerpoDoc [] = [
                    "numItem" => $this->convertIntegerValue($valueItem['item_order']),
                    "cantidad" => $this->convertToFloatValue($valueItem['qty']),
                    "codigo" => null,
                    "uniMedida" => 59,
                    "descripcion" => $valueItem['description'],
                    "precioUni" => $this->convertToFloatValue($valueItem['rate']),
                    "montoDescu" => 0.00,
                    "ventaGravada" => $this->convertToFloatValue(($valueItem['qty'] * $valueItem['rate'])),
                    "tributos" => null,
                    "noGravado" => 0.0,
                ];
                $sumatoria += $this->convertToFloatValue(($valueItem['rate'] / 1.13) * 0.13) * $this->convertIntegerValue($valueItem['qty']);
            }
            if ($clientGroup == "8") {
                $valorGranContribuyente = $sumatoriaVenta * 0.01;
            }
        }

        /*var_dump($detalleCuerpoDoc[1]["precioUni"]);
        exit;*/

        return [$sumatoria, $detalleCuerpoDoc, $sumatoriaVenta, $sumatoriaVentaExenta, $valorGranContribuyente];

    }

    public function generarJSON($tipoDocumento, $factCliente, $fechaFactEmi, $value, $sumatoriaIVA, $detalleCuerpoDoc, $sumatoriaVenta, $sumatoriaVentaExenta, $valorGranContribuyente)
    {


        $formatter = new NumeroALetras();
        $dteJSON = [];

        if ($tipoDocumento == "01") { // Factura

            $descuento = 0;
            $tributo = null;
            $ivaConDescuento = 0;

            if ($this->convertToFloatValue($value['adjustment']) != 0) {
                $descuento = $this->convertToFloatValue($value['adjustment'] * -1);
                $ivaConDescuento = ($sumatoriaVenta - $descuento) * 0.13;
            }


            $dteJSON = [
                "identificacion" => [
                    "version" => 1,
                    "ambiente" => null,
                    "tipoDte" => $tipoDocumento,
                    "numeroControl" => null,
                    "codigoGeneracion" => $value['code_to_mh'],
                    "tipoModelo" => 1,
                    "tipoOperacion" => 1,
                    "tipoContingencia" => null,
                    "motivoContin" => null,
                    "fecEmi" => $fechaFactEmi[0],
                    "horEmi" => $fechaFactEmi[1],
                    "tipoMoneda" => "USD"
                ],
                "documentoRelacionado" => null,
                "emisor" => [
                    "nit" => null,
                    "nrc" => null,
                    "nombre" => null,
                    "codActividad" => null,
                    "descActividad" => null,
                    "nombreComercial" => null,
                    "tipoEstablecimiento" => null,
                    "direccion" => [
                        "departamento" => null,
                        "municipio" => null,
                        "complemento" => null
                    ],
                    "telefono" => null,
                    "correo" => null,
                    "codEstableMH" => null,
                    "codEstable" => null,
                    "codPuntoVentaMH" => null,
                    "codPuntoVenta" => null
                ],
                "receptor" => [
                    "tipoDocumento" => "36",
                    "numDocumento" => (empty($factCliente->documento)) ? str_replace("-", "", $factCliente->nit) : str_replace("-", "", $factCliente->documento),
                    "nrc" => str_replace("-", "", $factCliente->documentoMH),
                    "nombre" => $factCliente->compania,
                    "codActividad" => $factCliente->actividad_economica,
                    "descActividad" => $factCliente->desc_actividad_economica,
                    "direccion" => [
                        "departamento" => $factCliente->deptoCodeMH,
                        "municipio" => $factCliente->muniCodeMH,
                        "complemento" => $factCliente->direccion],
                    "telefono" => $factCliente->telefono,
                    "correo" => $factCliente->mail
                ],
                "otrosDocumentos" => null,
                "ventaTercero" => null,
                "cuerpoDocumento" =>
                    $detalleCuerpoDoc,
                "resumen" => [
                    "totalNoSuj" => 0.0,
                    "totalExenta" => ($sumatoriaVentaExenta != 0) ? $this->convertToFloatValue($sumatoriaVentaExenta) : 0.0,
                    "totalGravada" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue($sumatoriaVenta + $sumatoriaIVA) : 0.0,
                    "subTotalVentas" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue($sumatoriaVenta + $sumatoriaIVA) : $this->convertToFloatValue($sumatoriaVentaExenta),
                    "descuNoSuj" => 0.0,
                    "descuExenta" => ($sumatoriaVentaExenta != 0) ? $this->convertToFloatValue($value['adjustment'] * -1) : 0.0,
                    "descuGravada" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue($value['adjustment'] * -1) : 0.0,
                    "porcentajeDescuento" => 0.0,
                    "totalDescu" => ($value['adjustment'] != 0) ? $this->convertToFloatValue($value['adjustment'] * -1) : 0.0,
                    "tributos" => $tributo,
                    "subTotal" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue($sumatoriaVenta + $sumatoriaIVA - $descuento) : $this->convertToFloatValue($sumatoriaVentaExenta - $descuento),
                    "ivaRete1" => $this->convertToFloatValue($valorGranContribuyente),
                    "reteRenta" => 0.0,
                    "montoTotalOperacion" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue($sumatoriaVenta + $sumatoriaIVA - $descuento) : $this->convertToFloatValue($sumatoriaVentaExenta - $descuento),
                    "totalNoGravado" => 0.0,
                    "totalPagar" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue(($sumatoriaVenta + $sumatoriaIVA) - $valorGranContribuyente - $descuento) : $this->convertToFloatValue($sumatoriaVentaExenta - $descuento),
                    "totalLetras" => $formatter->toWords(($sumatoriaVenta != 0) ? $this->convertToFloatValue(($sumatoriaVenta + $sumatoriaIVA) - $valorGranContribuyente - $descuento) : $this->convertToFloatValue($sumatoriaVentaExenta - $descuento)),
                    "totalIva" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue($sumatoriaIVA) : 0.0,
                    "saldoFavor" => 0.0,
                    "condicionOperacion" => ($value['forma_pago_id'] == "7" ? 1 : 2),
                    "pagos" => [
                        [
                            "codigo" => "01",
                            "montoPago" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue(($sumatoriaVenta + $sumatoriaIVA) - $valorGranContribuyente - $descuento) : $this->convertToFloatValue($sumatoriaVentaExenta - $descuento),
                            "plazo" => "01",
                            "referencia" => null,
                            "periodo" => 30
                        ]
                    ],
                    "numPagoElectronico" => null
                ],
                "extension" => null,
                "apendice" => null
            ];

        } else if ($tipoDocumento == "03") { // Comprobante de crédito fiscal

            $catalogoPlazosPeriodo = [
                8  => ["plazo" => "01", "periodo" => 8],
                9  => ["plazo" => "01", "periodo" => 15],
                10 => ["plazo" => "01", "periodo" => 30],
                11 => ["plazo" => "01", "periodo" => 45],
                12 => ["plazo" => "01", "periodo" => 60],
                13 => ["plazo" => "01", "periodo" => 90],
            ];

            $tributo = null;
            $descuento = 0;
            $ivaConDescuento = 0;

            if ($this->convertToFloatValue($value['adjustment']) != 0 && $value['adjustment'] < 0) {
                $descuento = $this->convertToFloatValue($value['adjustment'] * -1);
                $ivaConDescuento = ($sumatoriaVenta - $descuento) * 0.13;
            }

            if ($sumatoriaVenta > 0) {
                $tributo = [
                    [
                        "codigo" => "20",
                        "descripcion" => "Impuesto al Valor Agregado 13%",
                        "valor" => round(($ivaConDescuento > 0) ? $ivaConDescuento : $this->convertToFloatValue($sumatoriaIVA), 2)
                    ]
                ];
            }

            $dteJSON = [
                "identificacion" => [
                    "version" => 3,
                    "ambiente" => null,
                    "tipoDte" => $tipoDocumento,
                    "numeroControl" => null,
                    "codigoGeneracion" => $value['code_to_mh'],
                    "tipoModelo" => 1, // Puede ser 1 o 2
                    "tipoOperacion" => 1, // Puede ser 1 o 2
                    "tipoContingencia" => null,
                    "motivoContin" => null,
                    "fecEmi" => $fechaFactEmi[0],
                    "horEmi" => $fechaFactEmi[1],
                    "tipoMoneda" => "USD"
                ],
                "documentoRelacionado" => null,
                "emisor" => [
                    "nit" => null,
                    "nrc" => null,
                    "nombre" => null,
                    "codActividad" => null,
                    "descActividad" => null,
                    "nombreComercial" => null,
                    "tipoEstablecimiento" => null,
                    "direccion" => [
                        "departamento" => null,
                        "municipio" => null,
                        "complemento" => null
                    ],
                    "telefono" => null,
                    "correo" => null,
                    "codEstableMH" => null,
                    "codEstable" => null,
                    "codPuntoVentaMH" => null,
                    "codPuntoVenta" => null
                ],
                "receptor" => [
                    "nit" => (empty($factCliente->documento)) ? str_replace("-", "", $factCliente->nit) : str_replace("-", "", $factCliente->documento),
                    "nombreComercial" => $factCliente->compania,
                    "nrc" => str_replace("-", "", $factCliente->documentoMH),
                    "nombre" => $factCliente->compania,
                    "codActividad" => $factCliente->actividad_economica,
                    "descActividad" => $factCliente->desc_actividad_economica,
                    "direccion" => [
                        "departamento" => $factCliente->deptoCodeMH,
                        "municipio" => $factCliente->muniCodeMH,
                        "complemento" => $factCliente->direccion],
                    "telefono" => $factCliente->telefono,
                    "correo" => $factCliente->mail
                ],
                "otrosDocumentos" => null,
                "ventaTercero" => null,
                "cuerpoDocumento" =>
                    $detalleCuerpoDoc,
                "resumen" => [
                    "totalNoSuj" => 0.0,
                    "totalExenta" => ($sumatoriaVentaExenta != 0) ? $sumatoriaVentaExenta : 0.00,
                    "totalGravada" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue($sumatoriaVenta) : 0.00,
                    "subTotalVentas" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue($sumatoriaVenta) : $this->convertToFloatValue($sumatoriaVentaExenta),
                    "descuNoSuj" => 0.0,
                    "descuExenta" => 0.0,
                    "descuGravada" => $this->convertToFloatValue($value['adjustment'] * -1),
                    "porcentajeDescuento" => 0.0,
                    "totalDescu" => ($value['adjustment'] != 0) ? $this->convertToFloatValue($value['adjustment'] * -1) : 0.0,
                    "tributos" => $tributo,
                    "subTotal" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue(($sumatoriaVenta) - $descuento) : $this->convertToFloatValue($sumatoriaVentaExenta),
                    "ivaPerci1" => 0.0,
                    "ivaRete1" => $this->convertToFloatValue($valorGranContribuyente),
                    "reteRenta" => 0.0,
                    "montoTotalOperacion" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue(($sumatoriaVenta - $descuento + (($ivaConDescuento == 0) ? $sumatoriaIVA : $ivaConDescuento))) : $this->convertToFloatValue($sumatoriaVentaExenta),
                    "totalNoGravado" => 0.0,
                    "totalPagar" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue(($sumatoriaVenta - $descuento) + (($ivaConDescuento == 0) ? $sumatoriaIVA : $ivaConDescuento) - $valorGranContribuyente) : $this->convertToFloatValue($sumatoriaVentaExenta),
                    "totalLetras" => $formatter->toWords(($sumatoriaVenta != 0) ? $this->convertToFloatValue(($sumatoriaVenta - $descuento) + (($ivaConDescuento == 0) ? $sumatoriaIVA : $ivaConDescuento) - $valorGranContribuyente) : $this->convertToFloatValue($sumatoriaVentaExenta)),
                    "saldoFavor" => 0.0,
                    "condicionOperacion" => ($value['forma_pago_id'] == "7" ? 1 : 2),
                    "pagos" => [
                        [
                            "codigo" => "01",
                            "montoPago" => ($sumatoriaVenta != 0) ? $this->convertToFloatValue((($sumatoriaVenta - $descuento) + (($ivaConDescuento == 0) ? $sumatoriaIVA : $ivaConDescuento)) - $valorGranContribuyente) : $this->convertToFloatValue($sumatoriaVentaExenta),
                            "plazo" => ($value['forma_pago_id'] == "7" ? null :  $catalogoPlazosPeriodo[$value['forma_pago_id']]['plazo'] ),
                            "referencia" => "",
                            "periodo" => ($value['forma_pago_id'] == "7" ? null :  $catalogoPlazosPeriodo[$value['forma_pago_id']]['periodo'] )
                        ]
                    ],
                    "numPagoElectronico" => null
                ],
                "extension" => null,
                "apendice" => null
            ];

        } else if ($tipoDocumento == "11") { // Facturas de exportación
            $dteJSON = [
                "identificacion" => [
                    "version" => 1,
                    "ambiente" => null,
                    "tipoDte" => $tipoDocumento,
                    "numeroControl" => null,
                    "codigoGeneracion" => $value['code_to_mh'],
                    "tipoModelo" => 1, // Puede ser 1 o 2
                    "tipoOperacion" => 1, // Puede ser 1 o 2
                    "tipoContingencia" => null,
                    "motivoContigencia" => null,
                    "fecEmi" => $fechaFactEmi[0],
                    "horEmi" => $fechaFactEmi[1],
                    "tipoMoneda" => "USD"
                ],
                "emisor" => [
                    "nit" => null,
                    "nrc" => null,
                    "nombre" => null,
                    "codActividad" => null,
                    "descActividad" => null,
                    "nombreComercial" => null,
                    "tipoEstablecimiento" => null,
                    "direccion" => [
                        "departamento" => null,
                        "municipio" => null,
                        "complemento" => null
                    ],
                    "telefono" => null,
                    "correo" => null,
                    "codEstableMH" => null,
                    "codEstable" => null,
                    "codPuntoVentaMH" => null,
                    "codPuntoVenta" => null,
                    "tipoItemExpor" => 1,
                    "recintoFiscal" => null,//catrecintofiscal
                    "regimen" => null//catregimen
                ],
                "receptor" => [
                    "nombre" => $factCliente->compania,
                    "tipoDocumento" => "36",
                    "numDocumento" => $this->quitarGuiones((empty($factCliente->documento)) ? $factCliente->nit : $factCliente->documento),
                    "nombreComercial" => $factCliente->compania,
                    "codPais" => $factCliente->codpais,
                    "nombrePais" => $factCliente->nombre_pais,
                    "complemento" => $factCliente->direccion,
                    "tipoPersona" => 2,
                    "descActividad" => $factCliente->desc_actividad_economica,
                    "telefono" => $factCliente->telefono,
                    "correo" => $factCliente->mail
                ],
                "otrosDocumentos" => null,
                "ventaTercero" => null,
                "cuerpoDocumento" =>
                    $detalleCuerpoDoc,
                "resumen" => [
                    "totalGravada" => $this->convertToFloatValue($value['total']),
                    "descuento" => 0.00,
                    "porcentajeDescuento" => 0.0,
                    "totalDescu" => 0.0,
                    "seguro" => 0.00,
                    "flete" => 0.00,
                    "montoTotalOperacion" => $this->convertToFloatValue($value['total']),
                    "totalNoGravado" => 0.0,
                    "totalPagar" => $this->convertToFloatValue($value['total']),
                    "totalLetras" => $formatter->toWords($value['total']),
                    "condicionOperacion" => ($value['forma_pago_id'] == "7" ? 1 : 2),
                    "pagos" => null,
                    "codIncoterms" => null,
                    "descIncoterms" => null,
                    "numPagoElectronico" => null,
                    "observaciones" => null,
                ],
                "apendice" => null
            ];
        }


        return $dteJSON;

    }

    public function quitarGuiones($cadena)
    {
        return str_replace('-', '', $cadena);
    }

    public function termsfactura_get($generationcode)
    {
        $res = $this->facturasv_model->get_terms_factura($generationcode);
        $this->response($res, 200);
    }


    public function estimate_number_get($generationcode)
    {
        $res = $this->facturasv_model->get_estimate_factura($generationcode);
        $this->response($res, 200);
    }


}