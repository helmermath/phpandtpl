<?php if (version_compare(VERSION, '2.0', '>=')) { // v2.0.x Compatibility ?>



<?php if (!empty($error)) { ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>
<?php if(!empty($customer_vault_id) && $customer_vault_id != 0 && $check_save_card != 2 && !$card_expired) { ?>
<div>
    <div class="radio">
        <label><input type="radio" name="check_vault_card" value="1" class="check_vault_card" checked> Use Card on file ending in <?php echo $customer_card_number; ?></label>
    </div>
    <div class="radio">
        <label><input type="radio" name="check_vault_card" value="2" class="check_vault_card"> Use new card</label>
    </div>
</div>
<?php } ?>

<form action="<?php echo $action; ?>" id="form_old_card" method="<?php echo $form_method; ?>" class="form-horizontal" style="<?php if(empty($customer_vault_id) || $customer_vault_id == 0 || $check_save_card == 2 || $card_expired) { echo "display:none;"; } ?>">
<input type="hidden" name="is_card" value="1">
<?php if($show_cvv == 1) { ?>
<fieldset id="payment">
    <legend><?php echo $text_legend; ?></legend>
    <div class="form-group required">
        <label class="col-sm-2 control-label" for="card_cvv">Card CVV:</label>
        <div class="col-sm-10">
            <input type="text" name="card_cvv" value="" maxlength="4" placeholder="3-4 digit code" style="width: 150px;padding-left: 32px;" req="1" validate="" id="card_cvv_old" size="70" class="form-control">
            <i class="fa fa-lock" style="position: absolute;left: 27px;top: 9px;pointer-events: none;color: #888;font-size: 16px;"></i>

        </div>
    </div>
</fieldset>
<?php  } ?>
<div class="buttons">
    <div>
        <input type="submit" value="<?php echo $button_continue; ?>" id="button_old_card" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary col-sm-12" />
    </div>
</div>
</form>

<?php if (isset($testmode) && $testmode) { ?>
<div class="alert alert-info"><?php echo $text_testmode; ?></div>
<?php } ?>

<form action="<?php echo $action; ?>" id="form_<?php echo $classname; ?>" method="<?php echo $form_method; ?>" class="form-horizontal" style="<?php if(!empty($customer_vault_id) && $customer_vault_id != 0 && $check_save_card != 2 && !$card_expired) { echo 'display:none;'; } ?>">
  <input type="hidden" name="is_card" value="2">
  <fieldset id="payment">
    <legend><?php echo $text_legend; ?></legend>
      <div class="form-group">
          <div class="col-sm-10">
              <input name="card_type" id="card_type" type="hidden" id="cardjstype" value="visa">
              <link href="catalog/view/javascript/card-js/card-js.min.css" rel="stylesheet" type="text/css" />
              <script src="catalog/view/javascript/card-js/card-js.js"></script>
              <div class="card-js" data-capture-name="true">
                  <input class="card-number my-custom-class" req="1" name="card_num">
                  <input class="name" id="the-card-name-element" req="1" name="card_name">
                  <input class="expiry-month" name="card_mon">
                  <input class="expiry-year" name="card_year">
                  <input class="cvc" req="1" name="card_cvv">
              </div>
              <script src="catalog/view/javascript/card-js/card-js.jquery.js"></script>
          </div>
      </div>
      <?php if($check_save_card == 0) { ?>
      <div class="form-group">
          <div class="checkbox" style="margin-left: 18px;">
              <label><input type="checkbox" name="save_card" req="0" id="save_card" style="width: 13px;height: 13px;margin-top: 3px;" value="0" class="form-control"> Save Card</label>
          </div>
      </div>
      <?php } ?>
      <?php foreach ($fields as $field) { ?>

<?php if (!isset($field['no_open']) || !$field['no_open']) { ?>
    <div class="form-group<?php echo (!empty($field['required']) ) ? ' required' : ''; ?>">
<?php } ?>
<?php if (!isset($field['no_open']) || !$field['no_open']) { ?>
      <label class="col-sm-2 control-label" for="<?php echo ($field['name']) ?>"><?php echo ($field['entry']) ?></label>

      <div class="col-sm-10">
<?php } ?>

<?php if ($field['type'] == 'select') { ?>
      <select name="<?php echo ($field['name']) ?>" id="<?php echo ($field['name']) ?>" <?php echo (isset($field['multiple']) && $field['multiple']) ? 'multiple="multiple"' : ''?> <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> <?php echo (isset($field['size']) && $field['size']) ? 'size="' . $field['size'] . '"' : ''?> validate="<?php echo isset($field['validate']) ? $field['validate'] : ''; ?>" class="form-control">
<?php foreach ($field['options'] as $key => $value) { ?>
        <option value="<?php echo $key; ?>"<?php if((is_array($field['value']) && in_array($key, $field['value'])) || ($field['value'] == $key)) echo ' selected="selected"'?>><?php echo $value; ?></option>
<?php } ?>
      </select>
<?php } elseif ($field['type'] == 'radio') {?>
<?php foreach($field['options'] as $key => $value) : ?>
	  <input type="radio" name="<?php echo $field['name']; ?>" id="<?php echo $field['name']; ?>" value="<?php echo $key; ?>"<?php if($field['value'] == $key) echo ' checked="checked"'; ?> <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> validate="<?php echo isset($field['validate']) ? $field['validate'] : ''; ?>" class="form-control" /><label for="<?php echo $field['name']; ?>"><?php echo $value; ?></label>
<?php endforeach; ?>
<?php } elseif ($field['type'] == 'text') { ?>
      <input type="text" name="<?php echo ($field['name']) ?>" value="<?php echo $field['value']; ?>" placeholder="<?php echo (!empty($field['placeholder']) ) ? $field['placeholder'] : ''; ?>" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> req="<?php echo isset($field['required']) ? $field['required'] : ''; ?>" validate="<?php echo isset($field['validate']) ? $field['validate'] : ''; ?>" id="<?php echo ($field['name']) ?>" <?php echo (isset($field['size']) && $field['size']) ? 'size="' . $field['size'] . '"' : ''?> <?php echo (isset($field['maxlength']) && $field['maxlength']) ? 'maxlength="' . $field['maxlength'] . '"' : ''?> class="form-control" />
<?php } elseif ($field['type'] == 'password') { ?>
      <input type="password" name="<?php echo ($field['name']) ?>" value="<?php echo $field['value']; ?>" placeholder="<?php echo (!empty($field['placeholder']) ) ? $field['placeholder'] : ''; ?>" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> req="<?php echo isset($field['required']) ? $field['required'] : ''; ?>" validate="<?php echo isset($field['validate']) ? $field['validate'] : ''; ?>" id="<?php echo ($field['name']) ?>" <?php echo (isset($field['size']) && $field['size']) ? 'size="' . $field['size'] . '"' : ''?> <?php echo (isset($field['maxlength']) && $field['maxlength']) ? 'maxlength="' . $field['maxlength'] . '"' : ''?> class="form-control" />
<?php } elseif ($field['type'] == 'checkbox') { ?>
	  <input type="checkbox" name="<?php echo $field['name']; ?>" req="<?php echo isset($field['required']) ? $field['required'] : ''; ?>" validate="<?php echo isset($field['validate']) ? $field['validate'] : ''; ?>" id="<?php echo $field['name']; ?>" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> value="1"<?php if($field['value']) echo 'checked="checked"'; ?> class="form-control" />
<?php } elseif ($field['type'] == 'hidden') { ?>
	  <input type="hidden" name="<?php echo $field['name']; ?>" value="<?php echo $field['value']; ?>" id="<?php echo $field['name']; ?>" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> />
<?php } elseif ($field['type'] == 'textarea') {?>
	  <textarea name="<?php echo $field['name']; ?>" req="<?php echo isset($field['required']) ? $field['required'] : ''; ?>" validate="<?php echo isset($field['validate']) ? $field['validate'] : ''; ?>" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> id="<?php echo $field['name']; ?>" cols="<?php echo $field['cols']; ?>" rows="<?php echo $field['rows']; ?>" class="form-control"><?php echo $field['value']; ?></textarea>
<?php } elseif ($field['type'] == 'label') {?>
	  <label id="<?php echo $field['name']; ?>" class="form-control" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?>><?php echo $field['value']; ?></label>
<?php } elseif ($field['type'] == 'file') { ?>
	  <input req="<?php echo isset($field['required']) ? $field['required'] : ''; ?>" type="file" name="<?php echo $field['name']; ?>" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> />
<?php } // end if field type ?>

<?php if (!empty($field['help'])) { ?>
	  <span class="help-block" style="display:inline-block;"><?php echo $field['help']; ?></span>
<?php } ?>

<?php if (!isset($field['no_close']) || !$field['no_close']) { ?>
      </div>
    </div>
<?php } ?>

<?php } // end foreach $fields ?>
  </fieldset>

  <div class="buttons">
     <div class="pull-right">
	  <input type="submit" value="<?php echo $button_continue; ?>" id="button-confirm" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-primary" />
    </div>
  </div>

</form>



<?php } else { // 1.5.x version check ?>




<?php if (!empty($error)) { ?>
<div class="warning"><?php echo $error; ?></div>
<?php } ?>

<?php if (isset($testmode) && $testmode) { ?>
<div class="attention"><?php echo $text_testmode; ?></div>
<?php } ?>

<form action="<?php echo $action; ?>" id="form_<?php echo $classname; ?>" method="<?php echo $form_method; ?>" class="form-horizontal">
  <div id="payment">
    <h2><?php echo $text_legend; ?></h2>
    <table>

    <?php foreach ($fields as $field) { ?>

<?php if (!isset($field['no_open']) || !$field['no_open']) { ?>
    <tr>
    <td>
<?php echo (!empty($field['required']) ) ? '<span class="required">*</span>' : ''; ?>
      <label class="col-sm-2 control-label" for="<?php echo ($field['name']) ?>"><?php echo ($field['entry']) ?></label>
    </td>
    <td>
<?php } ?>

<?php if ($field['type'] == 'select') { ?>
      <select name="<?php echo ($field['name']) ?>" id="<?php echo ($field['name']) ?>" <?php echo (isset($field['multiple']) && $field['multiple']) ? 'multiple="multiple"' : ''?> <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> <?php echo (isset($field['size']) && $field['size']) ? 'size="' . $field['size'] . '"' : ''?> validate="<?php echo isset($field['validate']) ? $field['validate'] : ''; ?>" class="form-control">
<?php foreach ($field['options'] as $key => $value) { ?>
        <option value="<?php echo $key; ?>"<?php if((is_array($field['value']) && in_array($key, $field['value'])) || ($field['value'] == $key)) echo ' selected="selected"'?>><?php echo $value; ?></option>
<?php } ?>
      </select>
<?php } elseif ($field['type'] == 'radio') {?>
<?php foreach($field['options'] as $key => $value) : ?>
	  <input type="radio" name="<?php echo $field['name']; ?>" id="<?php echo $field['name']; ?>" value="<?php echo $key; ?>"<?php if($field['value'] == $key) echo ' checked="checked"'; ?> <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> validate="<?php echo isset($field['validate']) ? $field['validate'] : ''; ?>" class="form-control" /><label for="<?php echo $field['name']; ?>"><?php echo $value; ?></label>
<?php endforeach; ?>
<?php } elseif ($field['type'] == 'text') { ?>
      <input type="text" name="<?php echo ($field['name']) ?>" value="<?php echo $field['value']; ?>" placeholder="<?php echo (!empty($field['placeholder']) ) ? $field['placeholder'] : ''; ?>" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> req="<?php echo isset($field['required']) ? $field['required'] : ''; ?>" validate="<?php echo isset($field['validate']) ? $field['validate'] : ''; ?>" id="<?php echo ($field['name']) ?>" <?php echo (isset($field['size']) && $field['size']) ? 'size="' . $field['size'] . '"' : ''?> <?php echo (isset($field['maxlength']) && $field['maxlength']) ? 'maxlength="' . $field['maxlength'] . '"' : ''?> class="form-control" />
<?php } elseif ($field['type'] == 'password') { ?>
      <input type="password" name="<?php echo ($field['name']) ?>" value="<?php echo $field['value']; ?>" placeholder="<?php echo (!empty($field['placeholder']) ) ? $field['placeholder'] : ''; ?>" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> req="<?php echo isset($field['required']) ? $field['required'] : ''; ?>" validate="<?php echo isset($field['validate']) ? $field['validate'] : ''; ?>" id="<?php echo ($field['name']) ?>" <?php echo (isset($field['size']) && $field['size']) ? 'size="' . $field['size'] . '"' : ''?> <?php echo (isset($field['maxlength']) && $field['maxlength']) ? 'maxlength="' . $field['maxlength'] . '"' : ''?> class="form-control" />
<?php } elseif ($field['type'] == 'checkbox') { ?>
	  <input type="checkbox" name="<?php echo $field['name']; ?>" req="<?php echo isset($field['required']) ? $field['required'] : ''; ?>" validate="<?php echo isset($field['validate']) ? $field['validate'] : ''; ?>" id="<?php echo $field['name']; ?>" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> value="1"<?php if($field['value']) echo 'checked="checked"'; ?> class="form-control" />
<?php } elseif ($field['type'] == 'hidden') { ?>
	  <input type="hidden" name="<?php echo $field['name']; ?>" value="<?php echo $field['value']; ?>" id="<?php echo $field['name']; ?>" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> />
<?php } elseif ($field['type'] == 'textarea') {?>
	  <textarea name="<?php echo $field['name']; ?>" req="<?php echo isset($field['required']) ? $field['required'] : ''; ?>" validate="<?php echo isset($field['validate']) ? $field['validate'] : ''; ?>" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> id="<?php echo $field['name']; ?>" cols="<?php echo $field['cols']; ?>" rows="<?php echo $field['rows']; ?>" class="form-control"><?php echo $field['value']; ?></textarea>
<?php } elseif ($field['type'] == 'label') {?>
	  <label id="<?php echo $field['name']; ?>" class="form-control" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?>><?php echo $field['value']; ?></label>
<?php } elseif ($field['type'] == 'file') { ?>
	  <input req="<?php echo isset($field['required']) ? $field['required'] : ''; ?>" type="file" name="<?php echo $field['name']; ?>" <?php echo isset($field['param']) ? html_entity_decode($field['param']) : ''; ?> />
<?php } // end if field type ?>

<?php if (!empty($field['help'])) { ?>
	  <span class="help-block" style="display:inline-block;"><?php echo $field['help']; ?></span>
<?php } ?>

<?php if (!isset($field['no_close']) || !$field['no_close']) { ?>
	  </td>
      </tr>
<?php } ?>

<?php } // end foreach $fields ?>
    </table>
  </div>

  <div class="buttons" style="text-align: right;min-height:20px;">
    <div class="right">
      <?php /* <a id="button-confirm" class="button"><span><?php echo $button_continue; ?></span></a> */ ?>
	  <input type="submit" value="<?php echo $button_continue; ?>" id="button-confirm" class="button" />
    </div>
  </div>

</form>

<?php } // End version check ?>

<script type="text/javascript"><!--
    $('.check_vault_card').change(function(){
        $('#payment_message_error').remove();
        $('#payment_message_error1').remove();
        if ($(this).val() == '2') {
            $('#form_bff').show();
            $('#form_old_card').hide();
        } else {
            $('#form_bff').hide();
            $('#form_old_card').show();
        }
    });

    $("#card_cvv_old").keypress(function (e) {
        //if the letter is not digit then display error and don't type anything
        if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            //display error message
            return false;
        }
    });
    $('#save_card').change(function() {
        if($(this).is(":checked")) {
            $('#save_card').val('1');
        } else {
            $('#save_card').val('0');
        }
    });

    $('form#form_old_card').submit(function(e) {
        var error = false;
        e.preventDefault();
        // Validate Card first
        $.ajax({
            type: 'POST',
            url: 'index.php?route=extension/payment/<?php echo $classname; ?>/send',
            data: $('form#form_old_card :input'),
            dataType: 'json',
            beforeSend: function() {
                $('#cpf-error').remove();
                $('#payment_message_error1').remove();
                $('#button_old_card').attr('disabled', true);
                $('form#form_old_card').before('<div id="payment_message_wait1" class="attention alert alert-warning"><i class="fa fa-info-circle"></i> <?php echo $text_wait; ?></div>');
            },
            complete: function() {
                $('#button_old_card').attr('disabled', false);
                $('#payment_message_wait1').remove();
            },
            success: function(json) {
                if (json['html']) {
                    $(document.body).replaceWith(json['html']);
                }
                // if 3ds redirect instruction
                if (json['ACSURL']) {
                    $('#3dauth').remove();
                    html  = '<form action="' + json['ACSURL'] + '" method="post" id="3dauth">';
                    html += '  <input type="hidden" name="MD" value="' + json['MD'] + '" />';
                    html += '  <input type="hidden" name="PaReq" value="' + json['PaReq'] + '" />';
                    html += '  <input type="hidden" name="TermUrl" value="' + json['TermUrl'] + '" />';
                    html += '</form>';
                    $('form#<?php echo $classname; ?>').after(html);
                    $('#3dauth').submit();
                }
                // if error
                if (json['error']) {
                    $('form#form_old_card').before('<div id="payment_message_error1" class="warning alert alert-danger"><i class="fa fa-info-circle"></i> '+json['error']+'</div>');
                }
                // if success
                if (json['success']) {
                    location = json['success'];
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    });

    $('form#form_<?php echo $classname; ?>').submit(function(e) {

	var error = false;

	e.preventDefault();

	// Validate Card first
	$.ajax({
		type: 'POST',
		url: 'index.php?route=extension/payment/<?php echo $classname; ?>/send',
        data: $('form#form_<?php echo $classname; ?> :input'),
		dataType: 'json',
		beforeSend: function() {
		  $('#cpf-error').remove();
		  $('#payment_message_error').remove();
		  $('#button-confirm').attr('disabled', true);
		  $('form#<?php echo $classname; ?>').before('<div id="payment_message_wait" class="attention alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $text_wait; ?></div>');
		},
		complete: function() {
		  $('#button-confirm').attr('disabled', false);
		  $('#payment_message_wait').remove();
		},
		success: function(json) {
		  if (json['html']) {
			$(document.body).replaceWith(json['html']);
		  }

		  // if 3ds redirect instruction
		  if (json['ACSURL']) {
			$('#3dauth').remove();

			html  = '<form action="' + json['ACSURL'] + '" method="post" id="3dauth">';
			html += '  <input type="hidden" name="MD" value="' + json['MD'] + '" />';
			html += '  <input type="hidden" name="PaReq" value="' + json['PaReq'] + '" />';
			html += '  <input type="hidden" name="TermUrl" value="' + json['TermUrl'] + '" />';
			html += '</form>';

			$('form#<?php echo $classname; ?>').after(html);

			$('#3dauth').submit();
		  }

		  // if error
		  if (json['error']) {
			$('form#form_<?php echo $classname; ?>').before('<div id="payment_message_error" class="warning alert alert-warning"><i class="fa fa-info-circle"></i> '+json['error']+'</div>');
		  }

		  // if success
		  if (json['success']) {
			location = json['success'];
		  }
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});

});

$('select[name=card_type]').change(function(){
	if ($(this).val() == 'maestro' || $(this).val() == 'solo') {
		$('#solo').show();
	} else {
		$('#solo').hide();
	}
});

//# sourceURL=payment_dynamic.js
//--></script>