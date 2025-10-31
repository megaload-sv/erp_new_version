<?php defined('BASEPATH') or exit('No direct script access allowed'); 

$CI = &get_instance();
$_invoice_type = $CI->uri->segments[2];
$lTitle = "";
if($_invoice_type == "creditos"){
	$lTitle = "create_new_creditos";
}else
{
	$lTitle = "create_new_invoice";
}
?>

?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php
			echo form_open($this->uri->uri_string(),array('id'=>'invoice-form','class'=>'_transaction_form invoice-form'));
			if(isset($invoice)){
				echo form_hidden('isedit');
			}
			?>
            <div class="col-md-12">
                <?php $this->load->view('admin/creditos/invoice_template'); ?>
            </div>
            <?php echo form_close(); ?>
            <?php $this->load->view('admin/creditos_items/item'); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
const validate_creditos_form = (selector) => {
    selector = typeof(selector) == 'undefined' ? '#invoice-form' : selector;

    appValidateForm($(selector), {
        clientid: {
            required: {
                depends: function() {
                    var customerRemoved = $('select#clientid').hasClass('customer-removed');
                    return !customerRemoved;
                }
            }
        },
        date: 'required',
        currency: 'required',
        repeat_every_custom: {
            min: 1
        },
        number: {
            required: true,
        }
    });
    $("body").find('input[name="number"]').rules('add', {
        remote: {
            url: admin_url + "creditos/validate_invoice_number",
            type: 'post',
            data: {
                number: function() {
                    return $('input[name="number"]').val();
                },
                isedit: function() {
                    return $('input[name="number"]').data('isedit');
                },
                original_number: function() {
                    return $('input[name="number"]').data('original-number');
                },
                date: function() {
                    return $('input[name="date"]').val();
                },
            }
        },
        messages: {
            remote: app.lang.invoice_number_exists,
        }
    });
};


$(function() {

    validate_invoice_form();
    // Init accountacy currency symbol
    init_currency();
    // Project ajax search
    init_ajax_project_search_by_customer_id();
    // Maybe items ajax search
    init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');



});




const clientType = document.getElementById("clientType");

const onClientIdChange = (value) => {

    const idClient = value;
    if (idClient == undefined || idClient == "") {
        console.error("Id Is null");
        return;
    }


    init_ajax_search2("customer", {
        customer_id: value
    }, (customer) => {



        var html = ` `;
        console.log(customer);
        for (var key in customer) {
            var contact = customer[key];
            var selected = "";
            if (parseInt(customer_fields[0].value[0]) == parseInt(contact.id)) {
                selected = "selected";
            }
            html += `<option value="${contact.id}" ${selected}>${contact.name}</option>`;

        }
        console.log(html);
        document.getElementsByName("custom_fields[estimate][71][]")[0].parentElement.innerHTML = `<div  class="">
					 
						 
							<select name="custom_fields[estimate][71][]" id="custom_fields[estimate][71][]" class="  form-control ">${html}</select>
						
						</div>`;
        console.log(element);


    });
}

function init_ajax_search2(type, server_data, onCustomerFound) {

    let urlFinal = admin_url + "clients/contacts/" + server_data.customer_id;

    console.log(urlFinal);


    var data = {};
    data.type = "customer";
    data.rel_id = "";
    data.csrf_token_name = csrfData.formatted.csrf_token_name;
    data.q = "";
    if (typeof server_data != "undefined") {
        jQuery.extend(data, server_data);
    }
    console.log(data);




    var fd = new FormData();
    for (var i in data) {
        fd.append(i, data[i]);
    }
    console.log(fd);




    let urlFinal2 = admin_url + "clients/client/" + server_data.customer_id;
    $.ajax({
        url: urlFinal2,
        data: data,
        type: 'POST',
        success: function(response) {
            var div = document.createElement('div');
            div.innerHTML = response.trim();

            const htmlData = div;
            const finded = div.getElementsByClassName("_select_input_group")
            let select = finded[1].children;
            let tipoCliente = "";
            for (var i in select) {
                if (select[i].selected) {
                    tipoCliente = select[i].innerHTML;
                    break;
                }
            }

            console.log(tipoCliente);
            clientType.innerHTML = tipoCliente;
        }
    });
};
document.getElementById("clientid").onchange = (e) => {
    onClientIdChange(e.target.value);
}

const value = document.getElementById("clientid").value;
onClientIdChange(value);
</script>
</body>

</html>