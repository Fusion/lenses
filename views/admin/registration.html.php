				<h3>Registration</h3>
					<?=form_tag('admin/users/registration', array('method'=>'post', 'class'=>'jNice'))?>
						<fieldset>
<?php
						foreach($fields as $field)
						{
							echo	"<p><label for='{$field['name']}'>{$field['description']}</label>\n".
								Settings::present(
									$field['type'],
									$field['name'],
									$field['value'],
									$field['description'],
									$field['options'],
									$field['group']).
								"</p>\n";
						}
?>
						<?=input_hidden_tag('submitform')?>
						<?=submit_tag('Save')?>
						</fieldset>
					</form>
				<div>
					&nbsp;
				</div>
