<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Facturasv_model extends App_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_facturas_listas_para_mh()
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'invoices');
        $this->db->where('status', 2);
        $this->db->where('status_envio_mh', 1);
        return $this->db->get()->result_array();
    }

    public function get_facturas_listas_para_mh_by_code($code_to_mh)
    {
        $this->db->select('fac.*, ct.value as tipoVenta, concat(YEAR(est.date), \'-\', est.number) as estimateNumber');
        $this->db->from(db_prefix() . 'invoices as fac');
        $this->db->join(db_prefix() . 'customfieldsvalues as ct', 'ct.relid = fac.id AND ct.fieldid = 90', 'inner');
        $this->db->join(db_prefix() . 'estimates as est', 'est.invoiceid = fac.id', 'left');
        $this->db->where('fac.status', 2);
        $this->db->where('fac.status_envio_mh', 1);
        $this->db->where('fac.code_to_mh', $code_to_mh);
        return $this->db->get()->result_array();
    }

    public function get_detalle_factura_pagada($idFactura)
    {
        $this->db->select("item_order, qty, rate, '59' as unit, long_description, description");
        $this->db->from(db_prefix() . 'itemable');
        $this->db->where('rel_id', $idFactura);
        $this->db->where("(rel_type='invoice' OR rel_type='credito' )");
        return $this->db->get()->result_array();
    }

    public function get_cliente_factura($idCliente)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'clients');
        $this->db->where('userid', $idCliente);
        return $this->db->get()->row();
    }

    public function get_informacion_general($codigoPuntoVenta)
    {

        $factSV = $this->load->database('facturasv', TRUE);

        $factSV->select("emp.nit,
                       emp.nrc,
                       emp.nombreRazonSocial,
                       emp.codigoActividad,
                       emp.descripcionActividad,
                       emp.nombreComercial,
                       emp.telefono,
                       emp.correo,
                       suc.codigoTipoEstablecimiento,
                       suc.codigoDepartamento,
                       suc.codigoMunicipio,
                       suc.direccion,
                       suc.codigoMH,
                       suc.idSucursal,
                       pv.codigoPuntoVenta,
                       pv.codigoPuntoVentaMH,
                       seg.ambiente");
        $factSV->from('dteempresas emp');
        $factSV->join('sucursales suc', 'emp.codigoEmpresa = suc.codigoEmpresa');
        $factSV->join('puntosdeventa pv', 'suc.idSucursal = pv.idSucursal');
        $factSV->join('seguridad seg', 'emp.codigoEmpresa = seg.codigoEmpresa');
        $factSV->where('pv.codigoPuntoVenta', $codigoPuntoVenta);

        $query = $factSV->get();
        return $query->row();
    }

    public function get_correlativo_por_tipo_documento($tipoDocumento)
    {
        $factSV = $this->load->database('facturasv', TRUE);

        $factSV->select("concat('DTE-', codigoTipoDocumento, '-', suc.codigoMH, pv.codigoPuntoVentaMH, '-', LPAD(cor.correlativo,15,'0')) corre");
        $factSV->from('dteempresas emp');
        $factSV->join('sucursales suc', 'emp.codigoEmpresa = suc.codigoEmpresa');
        $factSV->join('puntosdeventa pv', 'suc.idSucursal = pv.idSucursal');
        $factSV->join('correlativosfacturas cor', 'suc.idSucursal = cor.idSucursal');
        $factSV->where('cor.codigoTipoDocumento', $tipoDocumento);
        $query = $factSV->get();
        return $query->row();

    }

    public function get_cliente_por_factura_id($factura_id)
    {

        $this->db->select('c.company as compania, 
                            d.long_name as departamento, 
                            d.codeMH as deptoCodeMH,
                            m.long_name as municipio, 
                            m.codeMH as muniCodeMH,
                            c.address as direccion,
                            c.phonenumber, 
                            cfv.value as telefono, 
                            cfvdoc.value as documentoMH, 
                            cfvmail.value as mail, 
                            cfvdui.value  as documento, 
                            g.name as giro, 
                            ae.codigo     as actividad_economica,
                            ae.valores    as desc_actividad_economica,
                            c.vat         as nit,
                            cg.groupid,
                            co.iso2 as codpais,
                            co.short_name as nombre_pais');
        $this->db->from(db_prefix() . 'invoices i');
        $this->db->join(db_prefix() . 'clients c', 'i.clientid = c.userid');
        $this->db->join(db_prefix() . 'departamentos d', 'd.dptoid_id = c.state');
        $this->db->join(db_prefix() . 'municipios m', 'm.municip_id = c.city');
        $this->db->join(db_prefix() . 'customfieldsvalues cfv', 'cfv.relid = c.userid and cfv.fieldid = 76', 'left');
        $this->db->join(db_prefix() . 'customfieldsvalues cfvdoc', 'cfvdoc.relid = c.userid and cfvdoc.fieldid = 65', 'left');
        $this->db->join(db_prefix() . 'customfieldsvalues cfvmail', 'cfvmail.relid = c.userid and cfvmail.fieldid = 77', 'left');
        $this->db->join(db_prefix() . 'customfieldsvalues cfvdui', 'cfvdui.relid = c.userid and cfvdui.fieldid = 70', 'left');
        $this->db->join(db_prefix() . 'giros g', 'g.id = c.giro', 'left');
        $this->db->join(db_prefix() . 'actividadeconomica_sv ae', 'ae.codigo = c.actividad_economica', 'left');
        $this->db->join(db_prefix() . 'customer_groups cg', 'c.userid = cg.customer_id', 'left');
        $this->db->join(db_prefix() . 'countries co', 'c.country = co.country_id', 'left');
        //$this->db->join(db_prefix() . 'customers_groups cgs','cgs.id = cg.groupid', 'left');
        $this->db->where('i.id', $factura_id);
        return $this->db->get()->row();

    }

    public function get_facturas_envio_mh($limit, $offset)
    {

        $this->db->select("case when prefix = 'FCF-' then 'Factura Consumidor Final'
                               when prefix = 'CRE-' then 'Factura Credito Fiscal'
                               when prefix = 'FEX-' then 'Factura de Exportacion'
                               end                                                                                tipoDoc,
                           concat(REPLACE(prefix, '-', ''), YEAR(enc.datecreated), '/', LPAD(enc.number, 3, '0')) internal_number,
                           cli.company,
                           DATE_FORMAT(enc.datecreated, '%d/%m/%Y')                                               date,
                           status_envio_mh,
                           code_to_mh");
        $this->db->from('tblinvoices enc');
        $this->db->join('tblclients cli', 'cli.userid = enc.clientid');
        $this->db->where('status_envio_mh = 1');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result_array();

    }

    public function count_facturas_envio_mh()
    {

        $this->db->from('tblinvoices enc');
        $this->db->join('tblclients cli', 'cli.userid = enc.clientid');
        $this->db->where('enc.status_envio_mh = 1');
        return $this->db->count_all_results();

    }

    public function set_factura_sellada($factCodeMH)
    {
        $data = array(
            'status_envio_mh' => 2,
        );

        $this->db->where('code_to_mh', $factCodeMH);
        $result = $this->db->update('tblinvoices', $data);

        return $result;

    }

    public function get_terms_factura($factCodeMH)
    {
        $this->db->select('tblinvoices.adminnote, CONCAT(YEAR(tblestimates.date), \'-\', tblestimates.number) as estimateNumber');
        $this->db->from('tblinvoices');
        $this->db->join('tblestimates', 'tblestimates.invoiceid = tblinvoices.id', 'left');
        $this->db->where('tblinvoices.code_to_mh', $factCodeMH);
        return $this->db->get()->row();

    }

    public function get_estimate_factura($factCodeMH)
    {
        $this->db->select('CONCAT(YEAR(tblestimates.date), \'-\', tblestimates.number) as estimateNumber');
        $this->db->from('tblinvoices');
        $this->db->join('tblestimates', 'tblestimates.invoiceid = tblinvoices.id', 'left');
        $this->db->where('tblinvoices.code_to_mh', $factCodeMH);
        return $this->db->get()->row();

    }

}