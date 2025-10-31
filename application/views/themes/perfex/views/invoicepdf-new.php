<?php

defined('BASEPATH') or exit('No direct script access allowed');


setlocale(LC_TIME, 'es_SV.UTF-8');

$pdf->SetFontSize (8);
$pdf->SetMargins(18, 10, 10, -1);
$pdf->SetPrintFooter(false);



$pdf->ln(34);


// Selecciona Registro de IVA
$pdf_custom_fields = get_custom_fields('customers',array('name'=>"RUC"));

foreach($pdf_custom_fields as $field){
    $ruc = get_custom_field_value($invoice->clientid,$field['id'],'customers');
}

// Selecciona forma de pago
$pdf_custom_fields = get_custom_fields('invoice',array('show_on_pdf'=>1,'show_on_client_portal'=>1));

foreach($pdf_custom_fields as $field){
    $fpago = get_custom_field_value($invoice->id,$field['id'],'invoice');
}

// Selecciona giro
$pdf_giros =  get_all_giros();

foreach($pdf_giros as $key => $value){
    if($value['id'] == $invoice->client->giro) {
        $desc_giro = $value['name'];brake;
    }
}

// Selecciona municipios
$pdf_municipios =  get_all_municipios();

foreach($pdf_municipios as $key => $value){
    if($value['municip_id'] == $invoice->client->city) {
        $pdf_municipio = $value['short_name'];brake;
    }
}

// Selecciona departamento

$pdf_departamentos =  get_all_departamentos();

foreach($pdf_departamentos as $key => $value){
    if($value['dptoid_id'] == $invoice->client->state) {
        $pdf_departamento = $value['short_name'];brake;
    }
}

// Selecciona giro
$pdf_giros =  get_all_giros();

foreach($pdf_giros as $key => $value){
    if($value['id'] == $invoice->client->giro) {
        $desc_giro = $value['name'];brake;
    }
}

// tipos de clientes

// $pdf_group_clients = get_customer_groups();

// foreach($pdf_group_clients as $key => $value){
//     if($value['customer_id'] == $invoice->clientid) {
//         $pdf_tipo_cliente = $value['groupid'];brake;

//     }
// }

// Retencion 1%

// $Valor_con_IVA = $invoice->tota * 1.13;

// $Retencion = 0;



// var_dump($group_clients);


// var_dump($invoice);

// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)

// $txt = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';

// Multicell test
$pdf->MultiCell(105, 6, $invoice->client->company, 0, 'L', 0, 0, 35, 39, true,0,false,true,12);
$pdf->MultiCell(60, 6, strftime(" %d %B %G", strtotime($invoice->date)), 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell(50, 5, $ruc, 0, 'C', 0, 0, '', '', true);
$pdf->MultiCell(50, 5, $invoice->client->vat, 0, 'R', 0, 0, '' ,'', true);
$pdf->MultiCell(70, 6, $desc_giro, 0, 'L', 0, 1, 140,43, true,0,false,true,12);
$pdf->MultiCell(150, 5, "        " . $invoice->client->address, 0, 'L', 0, 1, 30, 50, true);
$pdf->MultiCell(75, 5, $pdf_municipio, 0, 'C', 0, 0, '', '', true);
$pdf->MultiCell(75, 5, $pdf_departamento, 0, 'C', 0, 1, '', '', true);
$pdf->ln(5);
$pdf->MultiCell(120, 6, $fpago, 0, 'R', 0, 0, '', '', true);
$pdf->ln(23);




$totalitems = count($invoice->items);
$Lineas = 0;

// for ($x = 1; $x <= count($invoice->items); $x++) {
    foreach($invoice->items as $key => $value){

        $pdf->MultiCell(10, 5, $value['qty'], 0, 'L', 0, 0, '', '', true);
        $pdf->MultiCell(90, 5, $value['description'] .  chr(10) . $value['long_description'], 0, 'L', 0, 0, '', '', true);
        $pdf->MultiCell(35, 5, app_format_money($value['rate'], $invoice->currency_name), 0, 'R', 0, 0, '', '', true);
        $pdf->MultiCell(20, 5, "", 0, 'R', 0, 0, '', '', true);        
        $pdf->MultiCell(35, 5, app_format_money($value['qty'] * $value['rate'], $invoice->currency_name), 0, 'R', 0, 0, '', '', true);
        // $pdf->MultiCell(10, 5, '', 0, 'L', 0, 1, '', '', true);
        // $pdf->MultiCell(180, 5, $value['long_description'], 0, 'L', 0, 0, 40, '', true);       
        $pdf->MultiCell(180, 8, "", 0, 'L', 0, 1, '', '', true);

    }

// void writeHTMLCell( float $w, float $h, float $x, float $y, [string $html = ''], [mixed $border = 0], [int $ln = 0], [int $fill = 0], [boolean $reseth = true])
$pdf->writeHTMLCell(160, '', 30, 170, "<b> $invoice->adminnote </b>", 0, 1, 0, true, 'J', true);
$pdf->ln(5);
//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
$pdf->writeHTMLCell(20, 6, 187, 185, app_format_money($invoice->subtotal, $invoice->currency_name ), 0, 1, 0, true, 'R', true);
// $pdf->Cell(0, 8, app_format_money($invoice->subtotal, $invoice->currency_name ) , 0, 1, 'R', 0, '', 0, false, 'M', 'M');
$pdf->Cell(0, 8, app_format_money($invoice->total_tax, $invoice->currency_name ), 0, 1, 'R', 0, '', 0, false, 'M', 'M');
$pdf->Cell(0, 8, app_format_money($invoice->subtotal + $invoice->total_tax, $invoice->currency_name ), 0, 1, 'R', 0, '', 0, false, 'M', 'M');
$pdf->Cell(0, 9, '', 0, 1, 'R', 0, '', 0, false, 'M', 'M');
$pdf->Cell(0, 9, '', 0, 1, 'R', 0, '', 0, false, 'M', 'M');
$pdf->Cell(0, 9, app_format_money($invoice->total_tax_gcontrib, $invoice->currency_name ), 0, 1, 'R', 0, '', 0, false, 'M', 'M');

// if($pdf_tipo_cliente == 1) {
//     $Retencion = $invoice->subtotal * -0.01;
//     $pdf->Cell(0, 9, app_format_money($Retencion, $invoice->currency_name ), 0, 1, 'R', 0, '', 0, false, 'M', 'M');
// } else {
//     $pdf->Cell(0, 9, app_format_money($Retencion, $invoice->currency_name ), 0, 1, 'R', 0, '', 0, false, 'M', 'M');
// }


// $pdf->Cell(0, 9, app_format_money($invoice->total_tax_gcontrib, $invoice->currency_name ), 0, 1, 'R', 0, '', 0, false, 'M', 'M');
$pdf->Cell(0, 9, app_format_money($invoice->subtotal + $invoice->total_tax + $invoice->total_tax_gcontrib, $invoice->currency_name), 0, 1, 'R', 0, '', 0, false, 'M', 'M');

$pdf->writeHTMLCell(120, '', 30,186, $CI->numberword->convert($invoice->subtotal + $invoice->total_tax + $invoice->total_tax_gcontrib, $invoice->currency_name), 0, 1, 0, true, 'J', true);



// var_dump($invoice);

// $organization_info = '<div style="color:#424242;">';

// $organization_info .= format_organization_info();

// $organization_info .= '</div>';

// Bill to
// $invoice_info = '<b>' . _l('invoice_bill_to') . '</b>';
// $invoice_info .= '<div style="color:#424242;">';
//     $invoice_info .= format_customer_info($invoice, 'invoice', 'billing');
// $invoice_info .= '</div>';

// // ship to to
// if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
//     $invoice_info .= '<br /><b>' . _l('ship_to') . '</b>';
//     $invoice_info .= '<div style="color:#424242;">';
//     $invoice_info .= format_customer_info($invoice, 'invoice', 'shipping');
//     $invoice_info .= '</div>';
// }

// $invoice_info .= '<br />' . _l('invoice_data_date') . ' ' . _d($invoice->date) . '<br />';

// if (!empty($invoice->duedate)) {
//     $invoice_info .= _l('invoice_data_duedate') . ' ' . _d($invoice->duedate) . '<br />';
// }

// if ($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1) {
//     $invoice_info .= _l('sale_agent_string') . ': ' . get_staff_full_name($invoice->sale_agent) . '<br />';
// }

// if ($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1) {
//     $invoice_info .= _l('project') . ': ' . get_project_name_by_id($invoice->project_id) . '<br />';
// }

// foreach ($pdf_custom_fields as $field) {
//     $value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
//     if ($value == '') {
//         continue;
//     }
//     $invoice_info .= $field['name'] . ': ' . $value . '<br />';
// }

// $left_info  = $swap == '1' ? $invoice_info : $organization_info;
// $right_info = $swap == '1' ? $organization_info : $invoice_info;

// pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// // The Table
// $pdf->Ln(hooks()->apply_filters('pdf_info_and_table_separator', 6));

// // The items table
// $items = get_items_table_data_factura($invoice, 'invoice', 'pdf');

// print_r($items);



// var_dump($invoice);



// $tblhtml = $items->table_nohead();

// $pdf->writeHTML($tblhtml, true, false, false, false, '');

// $pdf->Ln(0);

// $tbltotal = '';
// $tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
// $tbltotal .= '
// <tr>
//     <td align="right" width="85%"><strong>' . _l('invoice_subtotal') . '</strong></td>
//     <td align="right" width="15%">' . app_format_money($invoice->subtotal, $invoice->currency_name) . '</td>
// </tr>';

// if (is_sale_discount_applied($invoice)) {
//     $tbltotal .= '
//     <tr>
//         <td align="right" width="85%"><strong>' . _l('invoice_discount');
//     if (is_sale_discount($invoice, 'percent')) {
//         $tbltotal .= '(' . app_format_number($invoice->discount_percent, true) . '%)';
//     }
//     $tbltotal .= '</strong>';
//     $tbltotal .= '</td>';
//     $tbltotal .= '<td align="right" width="15%">-' . app_format_money($invoice->discount_total, $invoice->currency_name) . '</td>
//     </tr>';
// }

// foreach ($items->taxes() as $tax) {
//     $tbltotal .= '<tr>
//     <td align="right" width="85%"><strong>' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</strong></td>
//     <td align="right" width="15%">' . app_format_money($tax['total_tax'], $invoice->currency_name) . '</td>
// </tr>';
// }

// if ((int) $invoice->adjustment != 0) {
//     $tbltotal .= '<tr>
//     <td align="right" width="85%"><strong>' . _l('invoice_adjustment') . '</strong></td>
//     <td align="right" width="15%">' . app_format_money($invoice->adjustment, $invoice->currency_name) . '</td>
// </tr>';
// }

// $tbltotal .= '
// <tr style="background-color:#f0f0f0;">
//     <td align="right" width="85%"><strong>' . _l('invoice_total') . '</strong></td>
//     <td align="right" width="15%">' . app_format_money($invoice->total, $invoice->currency_name) . '</td>
// </tr>';

// if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) {
//     $tbltotal .= '
//     <tr>
//         <td align="right" width="85%"><strong>' . _l('invoice_total_paid') . '</strong></td>
//         <td align="right" width="15%">-' . app_format_money(sum_from_table(db_prefix().'invoicepaymentrecords', [
//         'field' => 'amount',
//         'where' => [
//             'invoiceid' => $invoice->id,
//         ],
//     ]), $invoice->currency_name) . '</td>
//     </tr>';
// }

// if (get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)) {
//     $tbltotal .= '
//     <tr>
//         <td align="right" width="85%"><strong>' . _l('applied_credits') . '</strong></td>
//         <td align="right" width="15%">-' . app_format_money($credits_applied, $invoice->currency_name) . '</td>
//     </tr>';
// }

// if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != Invoices_model::STATUS_CANCELLED) {
//     $tbltotal .= '<tr style="background-color:#f0f0f0;">
//        <td align="right" width="85%"><strong>' . _l('invoice_amount_due') . '</strong></td>
//        <td align="right" width="15%">' . app_format_money($invoice->total_left_to_pay, $invoice->currency_name) . '</td>
//    </tr>';
// }

// $tbltotal .= '</table>';
// $pdf->writeHTML($tbltotal, true, false, false, false, '');

// if (get_option('total_to_words_enabled') == 1) {
//     // Set the font bold
//     $pdf->SetFont($font_name, 'B', $font_size);
//     $pdf->Cell(0, 0, _l('') . '' . $CI->numberword->convert($invoice->total, $invoice->currency_name), 0, 1, 'L', 0, '', 0);
//     // Set the font again to normal like the rest of the pdf
//     $pdf->SetFont($font_name, '', $font_size);
//     $pdf->Ln(4);
// }

// if (count($invoice->payments) > 0 && get_option('show_transactions_on_invoice_pdf') == 1) {
//     $pdf->Ln(4);
//     $border = 'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
//     $pdf->SetFont($font_name, 'B', $font_size);
//     $pdf->Cell(0, 0, _l('invoice_received_payments'), 0, 1, 'L', 0, '', 0);
//     $pdf->SetFont($font_name, '', $font_size);
//     $pdf->Ln(4);
//     $tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
//         <tr height="20"  style="color:#000;border:1px solid #000;">
//         <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_number_heading') . '</th>
//         <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_mode_heading') . '</th>
//         <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_date_heading') . '</th>
//         <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_amount_heading') . '</th>
//     </tr>';
//     $tblhtml .= '<tbody>';
//     foreach ($invoice->payments as $payment) {
//         $payment_name = $payment['name'];
//         if (!empty($payment['paymentmethod'])) {
//             $payment_name .= ' - ' . $payment['paymentmethod'];
//         }
//         $tblhtml .= '
//             <tr>
//             <td>' . $payment['paymentid'] . '</td>
//             <td>' . $payment_name . '</td>
//             <td>' . _d($payment['date']) . '</td>
//             <td>' . app_format_money($payment['amount'], $invoice->currency_name) . '</td>
//             </tr>
//         ';
//     }
//     $tblhtml .= '</tbody>';
//     $tblhtml .= '</table>';
//     $pdf->writeHTML($tblhtml, true, false, false, false, '');
// }

// if (found_invoice_mode($payment_modes, $invoice->id, true, true)) {
//     $pdf->Ln(4);
//     $pdf->SetFont($font_name, 'B', $font_size);
//     $pdf->Cell(0, 0, _l('invoice_html_offline_payment'), 0, 1, 'L', 0, '', 0);
//     $pdf->SetFont($font_name, '', $font_size);

//     foreach ($payment_modes as $mode) {
//         if (is_numeric($mode['id'])) {
//             if (!is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
//                 continue;
//             }
//         }
//         if (isset($mode['show_on_pdf']) && $mode['show_on_pdf'] == 1) {
//             $pdf->Ln(1);
//             $pdf->Cell(0, 0, $mode['name'], 0, 1, 'L', 0, '', 0);
//             $pdf->Ln(2);
//             $pdf->writeHTMLCell('', '', '', '', $mode['description'], 0, 1, false, true, 'L', true);
//         }
//     }
// }

// if (!empty($invoice->clientnote)) {
//     $pdf->Ln(4);
//     $pdf->SetFont($font_name, 'B', $font_size);
//     $pdf->Cell(0, 0, _l('invoice_note'), 0, 1, 'L', 0, '', 0);
//     $pdf->SetFont($font_name, '', $font_size);
//     $pdf->Ln(2);
//     $pdf->writeHTMLCell('', '', '', '', $invoice->clientnote, 0, 1, false, true, 'L', true);
// }



