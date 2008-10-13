<div id="content">
        <div id="colOne">
		<h2 class="title"><a href='#'>Login</a></h2>
		<br />
		<div class="story">
			<?=form_tag('user/login' . $getStr, array('method'=>'post', 'id'=>'loginform', 'class'=>'uniForm'))?>
				<fieldset class="blockLabels">
					<div class='ctrlHolder'>
						<?=label_for('username', '<em>*</em> Your user name')?>
						<?=input_tag('username', $input['username'], array('class'=>'textInput'))?>
					</div>
					<div class='ctrlHolder'>
						<?=label_for('password', '<em>*</em> Your password')?>
						<?=input_password_tag('password', null, array('class'=>'textInput'))?>
					</div>
					<div class='buttonHolder'>
						<?=input_hidden_tag('submitform')?>
						<?=submit_tag('Log in', array('class'=>'submitButton'))?>
					</div>
<?php
if($controller == 'user' && $action == 'login')
{
	// Do not want login to take us to...login
	echo input_hidden_tag('fwc', 'main') . "\n";
	echo input_hidden_tag('fwa', 'index') . "\n";
}
else
{
	echo input_hidden_tag('fwc', $controller) . "\n";
	echo input_hidden_tag('fwa', $action) . "\n";
}
foreach($_REQUEST as $key => $value)
{
	if($key == 'commit' || $key == 'submitform') continue;
	echo input_hidden_tag($key, $value) . "\n";
}
?>
					<br />
					<br />
					<?=link_to("I do not have an account: I want to register (it's free!)", 'user/register')?>
				</fieldset>
			</form>
		</div>
	</div>
</div>
