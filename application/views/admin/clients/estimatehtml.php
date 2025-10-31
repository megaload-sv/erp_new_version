<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="mtop15 preview-top-wrapper">
   <div class="row">
      <div class="col-md-3">
         <div class="mbot30">
            <div class="estimate-html-logo">
               <?php echo get_dark_company_logo(); ?>
            </div>
         </div>
      </div>
      <div class="clearfix"></div>
   </div>
   <div class="top" data-sticky data-sticky-class="preview-sticky-header">
      <div class="container preview-sticky-container">
         <div class="row">
            <div class="col-md-12">
               <div class="pull-left">
                  <h3 class="bold no-mtop estimate-html-number no-mbot">
                     <span class="sticky-visible hide">
                     <?php echo format_estimate_number($estimate->id); ?>
                     </span>
                  </h3>
                  <h4 class="estimate-html-status mtop7">
                     <?php echo format_estimate_status($estimate->status,'',true); ?>
                  </h4>
               </div>
               <div class="visible-xs">
                  <div class="clearfix"></div>
               </div>
               <?php
                  // Is not accepted, declined and expired
                  if ($estimate->status != 4 && $estimate->status != 3 && $estimate->status != 5) {
                    $can_be_accepted = true;
                    if($identity_confirmation_enabled == '0'){
                      echo form_open($this->uri->uri_string(), array('class'=>'pull-right mtop7 action-button'));
                      echo form_hidden('estimate_action', 4);
                      echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_estimate').'</button>';
                      echo form_close();
                    } else {
                      echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_estimate').'</button>';
                    }
                  } else if($estimate->status == 3){
                    if (($estimate->expirydate >= date('Y-m-d') || !$estimate->expirydate) && $estimate->status != 5) {
                      $can_be_accepted = true;
                      if($identity_confirmation_enabled == '0'){
                        echo form_open($this->uri->uri_string(),array('class'=>'pull-right mtop7 action-button'));
                        echo form_hidden('estimate_action', 4);
                        echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_estimate').'</button>';
                        echo form_close();
                      } else {
                        echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_estimate').'</button>';
                      }
                    }
                  }
                  // Is not accepted, declined and expired
                  if ($estimate->status != 4 && $estimate->status != 3 && $estimate->status != 5) {
                    echo form_open($this->uri->uri_string(), array('class'=>'pull-right action-button mright5 mtop7'));
                    echo form_hidden('estimate_action', 3);
                    echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-default action-button accept"><i class="fa fa-remove"></i> '._l('clients_decline_estimate').'</button>';
                    echo form_close();
                  }
                  ?>
               <?php echo form_open($this->uri->uri_string(), array('class'=>'pull-right action-button')); ?>
               <button type="submit" name="estimatepdf" class="btn btn-default action-button download mright5 mtop7" value="estimatepdf">
               <i class="fa fa-file-pdf-o"></i>
               <?php echo _l('clients_invoice_html_btn_download'); ?>
               </button>
               <?php echo form_close(); ?>
               <?php if(is_client_logged_in() && has_contact_permission('estimates')){ ?>
               <a href="<?php echo site_url('clients/estimates/'); ?>" class="btn btn-default pull-right mright5 mtop7 action-button go-to-portal">
               <?php echo _l('client_go_to_dashboard'); ?>
               </a>
               <?php } ?>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="clearfix"></div>
<div class="panel_s mtop20">
   <div class="panel-body">
      <div class="col-md-10 col-md-offset-1">
         <div class="row mtop20">
            <div class="col-md-12 col-sm-12 transaction-html-info-col-left">
               <div class="col-md-2 col-sm-2 text-center">
                  <div class="estimate-html-logo">
                     <?php echo  get_company_logo(); ?>
                  </div>
               </div>
               <div class="col-md-8 col-sm-8 text-center">
                  <address class="estimate-html-company-info">
                     <?php echo format_organization_info(); ?>
                  </address>
               </div>
               <div class="col-md-2 col-sm-2 text-right">
                  <h4 class="bold estimate-html-number"><?php echo format_estimate_number($estimate->id); ?></h4>
               </div>
            </div>
         </div>
         <div class="row mtop20">
            <div class="col-md-12 col-sm-12 text-right">
            <?php setlocale(LC_ALL,"es_SV");
            echo utf8_encode( strftime("%A, %d de %B de %Y",  strtotime($estimate->datecreated)));
                  //setlocale(LC_ALL,NULL);
            ?>
            </div>
            <div class="col-md-12 col-sm-12 text-left">
            <p>Señores:</p>                
            <p><b><?php echo strtoupper( $estimate->client->company )?></b></p>
            <p>Atencion: <b>
            <?php
            $preferer_contact="";
            foreach ($estimate->preferer_contact as $k => $val) {
               $preferer_contact .= $val->firstname." ".$val->lastname ."<br>";
            }
            echo substr($preferer_contact, 0,-4);

             ?></b></p>
            </div>
                  <div class="col-md-12 col-sm-12 text-justify">
                     <p>Estimados Señores:</p>
                     <p>Reciban un cordial saludo y nuestros mejores deseos en sus proyectos empresariales.</br>
                        Por ese medio ponemos a su consideración los servicios solicitados según sus requerimientos: </p>
                     <p><?php echo ucfirst( $estimate->adminnote )?></p>
                  </div>
         </div>
         <div class="row">
            <div class="col-md-12">
               <div class="table-responsive">
                  <?php
                     $items = get_items_table_data($estimate, 'estimate');
                     echo $items->table();
                     ?>
               </div>
            </div><!-- ----------EDX-------- -->
            <?php
            $custom_fields = get_custom_fields('estimate');
            foreach ($custom_fields as $val) {
               if($val['slug']=="estimate_mostrar_total"){
                  $custom_id = $val["id"]; break;
               }
            }
            $value = get_custom_field_value($estimate->id,$custom_id,'estimate');

            if ($value != "NO") {
               ?>
            <div class="col-md-6 col-md-offset-6">
               <table class="table text-right">
                  <tbody>
                     <tr id="subtotal">
                        <td><span class="bold"><?php echo _l('estimate_subtotal'); ?></span>
                        </td>
                        <td class="subtotal">
                           <?php echo app_format_money($estimate->subtotal, $estimate->currency_name); ?>
                        </td>
                     </tr>
                     <?php if(is_sale_discount_applied($estimate)){ ?>
                     <tr>
                        <td>
                           <span class="bold"><?php echo _l('estimate_discount'); ?>
                           <?php if(is_sale_discount($estimate,'percent')){ ?>
                           (<?php echo app_format_number($estimate->discount_percent,true); ?>%)
                           <?php } ?></span>
                        </td>
                        <td class="discount">
                           <?php echo '-' . app_format_money($estimate->discount_total, $estimate->currency_name); ?>
                        </td>
                     </tr>
                     <?php } ?>
                     <?php
                        foreach($items->taxes() as $tax){
                         echo '<tr class="tax-area"><td class="bold">'.$tax['taxname'].' ('.app_format_number($tax['taxrate']).'%)</td><td>'.app_format_money($tax['total_tax'], $estimate->currency_name).'</td></tr>';
                        }
                        ?>
                     <?php if((int)$estimate->adjustment != 0){ ?>
                     <tr>
                        <td>
                           <span class="bold"><?php echo _l('estimate_adjustment'); ?></span>
                        </td>
                        <td class="adjustment">
                           <?php echo app_format_money($estimate->adjustment, $estimate->currency_name); ?>
                        </td>
                     </tr>
                     <?php } ?>
                     <tr>
                        <td><span class="bold"><?php echo _l('estimate_total'); ?></span>
                        </td>
                        <td class="total">
                           <?php echo app_format_money($estimate->total, $estimate->currency_name); ?>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div>
            <?php
         }
               if(get_option('total_to_words_enabled') == 1){ ?>
            <div class="col-md-12 text-center estimate-html-total-to-words">
               <p class="bold"><?php echo  _l('num_word').': '.$this->numberword->convert($estimate->total,$estimate->currency_name); ?></p>
            </div>
            <?php } ?>
            <?php if(count($estimate->attachments) > 0 && $estimate->visible_attachments_to_customer_found == true){ ?>
            <div class="clearfix"></div>
            <div class="estimate-html-files">
               <div class="col-md-12">
                  <hr />
                  <p class="bold mbot15 font-medium"><?php echo _l('estimate_files'); ?></p>
               </div>
               <?php foreach($estimate->attachments as $attachment){
                  // Do not show hidden attachments to customer
                  if($attachment['visible_to_customer'] == 0){continue;}
                  $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                  if(!empty($attachment['external'])){
                  $attachment_url = $attachment['external_link'];
                  }
                  ?>
               <div class="col-md-12 mbot15">
                  <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                  <a href="<?php echo $attachment_url; ?>"><?php echo $attachment['file_name']; ?></a>
               </div>
               <?php } ?>
            </div>
            <?php } ?>
            <?php if(!empty($estimate->clientnote)){ ?>
            <div class="col-md-12 estimate-html-note">
               <b><?php echo _l('estimate_note'); ?></b><br /><br /><?php echo $estimate->clientnote; ?>
            </div>
            <?php } ?>
            <?php if(!empty($estimate->terms)){ ?>
            <div class="col-md-12 estimate-html-terms-and-conditions text-center">
               <hr />
               <b><?php echo _l('terms_and_conditions'); ?></b><br /><br /><?php echo $estimate->terms; ?>
            </div>
            <?php } ?>
         </div>
      </div>
   </div>
</div>
<?php
   if($identity_confirmation_enabled == '1' && $can_be_accepted){
    get_template_part('identity_confirmation_form',array('formData'=>form_hidden('estimate_action',4)));
   }
   ?>
<script>
   $(function(){
     new Sticky('[data-sticky]');
   })
</script>
