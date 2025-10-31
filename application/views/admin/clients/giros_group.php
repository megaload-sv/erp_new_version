<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="customer_giros_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button group="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('customer_giro_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('customer_giro_add_heading'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/clients/group',array('id'=>'customer-giros-modal')); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('name','customer_giro_name'); ?>
                        <?php echo form_hidden('id'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button group="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button group="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script>
    window.addEventListener('load',function(){
       appValidateForm($('#customer-giros-modal'), {
        name: 'required'
    }, manage_customer_giros);

       $('#customer_giros_modal').on('show.bs.modal', function(e) {
        var invoker = $(e.relatedTarget);
        var giro_id = $(invoker).data('id');
        $('#customer_giros_modal .add-title').removeClass('hide');
        $('#customer_giros_modal .edit-title').addClass('hide');
        $('#customer_giros_modal input[name="id"]').val('');
        $('#customer_giros_modal input[name="name"]').val('');
        // is from the edit button
        if (typeof(giros_id) !== 'undefined') {
            $('#customer_giros_modal input[name="id"]').val(giros_id);
            $('#customer_giros_modal .add-title').addClass('hide');
            $('#customer_giros_modal .edit-title').removeClass('hide');
            $('#customer_giros_modal input[name="name"]').val($(invoker).parents('tr').find('td').eq(0).text());
        }
    });
   });
    function manage_customer_giros(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                if($.fn.DataTable.isDataTable('.table-customer-giros')){
                    $('.table-customer-giros').DataTable().ajax.reload();
                }
                if($('body').hasClass('dynamic-create-giros') && typeof(response.id) != 'undefined') {
                    var giros = $('select[name="giros_in[]"]');
                    giros.prepend('<option value="'+response.id+'">'+response.name+'</option>');
                    giros.selectpicker('refresh');
                }
                alert_float('success', response.message);
            }
            $('#customer_giros_modal').modal('hide');
        });
        return false;
    }

</script>
