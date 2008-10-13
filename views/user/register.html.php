<script type="text/javascript">
	function check_availability()
	{
		var username = $('#username').val();
		if(username=='')
			return false;
		UserController$checkUsernameAvailable(username, check_availability_cb);
		return false;
	}
	function check_availability_cb(avail)
	{
		var txt = avail ? '<span style="color:green">available</span>' : '<span style="color:red">unavailable</span>';
		$('#availableornot').html('&nbsp;<em>' + txt + '</em>');
	}
	
	$(document).ready(
		function(){
			$('#checkavailability').click(function() {return check_availability()})
		});
</script>
<div id="content">
        <div id="colOne">
		<h2 class="title"><a href='#'>Register, it's easy!</a></h2>
		<br />
		<div class="story">
			<?=form_tag('user/register', array('method'=>'post', 'id'=>'regform', 'class'=>'uniForm'))?>
				<fieldset class="blockLabels">
					<!--
					<div class='ctrlHolder'>
						<?=label_for('oid', '<em>*</em> Your Open Id')?>
						<?=input_tag('oid', null, array('class'=>'textInput'))?>
						<p class="formHint">What is an Open Id?</p>
					</div>
					-->
					<div class='ctrlHolder'>
						<?=label_for('username', '<em>*</em> Pick a user name')?>
						<?=input_tag('username', $input['username'], array('class'=>'textInput'))?>
						&nbsp;
						<?=link_to('Check availability', '#', array('id'=>'checkavailability'))?><span id='availableornot'></span>
					</div>
					<div class='ctrlHolder'>
						<?=label_for('password', '<em>*</em> Choose a password')?>
						<?=input_password_tag('password', null, array('class'=>'textInput'))?>
					</div>
					<div class='ctrlHolder'>
						<?=label_for('confirmpassword', '<em>*</em> Confirm password')?>
						<?=input_password_tag('confirmpassword', null, array('class'=>'textInput'))?>
					</div>
					<div class='ctrlHolder'>
						<?=label_for('email', '<em>*</em> Your email address')?>
						<?=input_tag('email', $input['email'], array('class'=>'textInput'))?>
						<p class="formHint">In case you lose your password</p>
					</div>
<?php
if(Config::$settings['users.registration.requireinvite']=='Yes'):
?>
					<div class='ctrlHolder'>
						<?=label_for('invite', '<em>*</em> Your invitation code')?>
						<?=input_tag('invite', $input['invite'], array('class'=>'textInput'))?>
						<p class="formHint"><?=link_to('No invitation?', 'user/inviteme', array('target'=>'_top'))?></p>
					</div>
<?php
endif
?>
<?php
if(!empty($captcha)):
?>
<script>
var RecaptchaOptions = {
   theme : 'white'
};
</script>
					<div class='ctrlHolder'>
						<?=label_for('captcha', '<em>*</em> Please verify both words below (you\'re not a robot!)')?>
						<?=$captcha?>
					</div>
<?php
endif
?>
					<div class='buttonHolder'>
						<?=input_hidden_tag('submitform')?>
						<?=submit_tag('Register', array('class'=>'submitButton'))?>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>
