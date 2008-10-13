<div id="content">
        <div id="colOne">
		<h2 class="title"><a href='#'>Request an invitation</a></h2>
		<br />
		<div class="story">
			<?=form_tag('user/inviteme', array('method'=>'post', 'id'=>'regform', 'class'=>'uniForm'))?>
				<fieldset class="blockLabels">
					<div class='ctrlHolder'>
					<div class='ctrlHolder'>
						<?=label_for('email', '<em>*</em> Your email address')?>
						<?=input_tag('email', $input['email'], array('class'=>'textInput'))?>
					</div>
					<div class='ctrlHolder'>
						<?=label_for('code', 'Code (Optional)')?>
						<?=input_tag('code', $input['code'], array('class'=>'textInput'))?>
					</div>
					<div class='buttonHolder'>
						<?=input_hidden_tag('submitform')?>
						<?=submit_tag('Register', array('class'=>'submitButton'))?>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>
