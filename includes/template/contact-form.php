<?php

//* Protection Check
if(!defined('ABSPATH')){
    die('You cannot be here');
 }
?>

<?php if(get_plugin_options('contact_plugin_active')):?>
<div id="form_success" style="background: green; color: white;"></div>
<div id="form_error" style="background: red; color: white;"></div>

<form action="" id="inquiry_form">

    <?php wp_nonce_field('wp_rest');?>

    <div id="form-top-row">
        <label for="name">Name</label>
        <input id="name" type="text" name="name" placeholder="Charlotte Bronte">

        <label for="email">Email</label><br/>
        <input id="email" type="text" name="email" placeholder="cbell@email.com">

        <label for="phone">Phone Number</label><br/>
        <input id="phone-number" type="text" name="phone" placeholder="(***)***-****">
    </div>
    <div id="form-bottom-row">
        <label for="message">Your message</label>
        <textarea name="message" id="" cols="30" rows="10"></textarea>
        <button type="submit">Submit form</button>
    </div>
</form>

<script>

    jQuery(document).ready( function($){

        $("#inquiry_form").submit( function(event){

            event.preventDefault();
            
            var form = $(this);

            $.ajax({

                type: "POST",
                url: "<?php echo get_rest_url(null, '/v1/contact-form/submit');?>",
                data: form.serialize(),
                success: function(res) {
                    form.hide();
                    $("#form_success").html(res).fadeIn();
                },
                error: function() {
                    $("#form_error").html("There was an error submitting your form.").fadeIn();
                }

            })


        });

    });
</script>
<?php else:?>
    This form is not active.
<?php endif;?>
