<?php

//* Protection Check
if(!defined('ABSPATH')){
    die('You cannot be here');
 }
?>

<?php if(get_plugin_options('contact_plugin_active')):?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Encode+Sans:wght@900&family=Quicksand&display=swap" rel="stylesheet">

<div id="form_success" style="background: green; color: white;"></div>
<div id="form_error" style="background: red; color: white;"></div>

<form action="" id="inquiry_form">

    <?php wp_nonce_field('wp_rest');?>
    <div class="header">
        <h2 id="title">Contact Me</h2>
    </div>
    <div id="form-top-row">
            <input id="name" type="text" name="name" placeholder="Name" required/>
        <input id="email" type="text" name="email" placeholder="Email" required/>
        <span class="dashicons dashicons-admin-site"></span>        
        <input id="phone-number" type="text" name="phone" placeholder="Phone Number">
    </div>

    <div id="form-bottom-row">
        <textarea name="message" id="" cols="30" rows="10" placeholder="Message"></textarea>
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
