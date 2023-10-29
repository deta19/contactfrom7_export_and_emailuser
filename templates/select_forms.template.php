<div class="wrapper">
	<?php
		if( !empty( $_GET['success'] ) && $_GET['success'] == 0 ) {
			echo '<div class="success error">submited with success</div>';
		} else if( !empty( $_GET['success'] ) && $_GET['success'] == 1 ) {
			echo '<div class="success error">can not be submited</div>';

		}
	?>
	<form id="contacform7_forms" action="<?php echo admin_url( 'admin-post.php' );?>" method="POST">
		<input type="hidden" name="action" value="submit_contactform_id">
	 	<?php wp_nonce_field( 'submit_contactform_id', 'sendcf7id' ); ?>
	 	<div class="input_wrapper">
			<label for="target_email"><input type="text" id="target_email" name="target_email" value="" placeholder="email to send"></label><br>
		<div>
		<div class="input_wrapper">
			<label for="email_suject"><input type="text" id="email_suject" name="email_subject" value="" placeholder="email subject"></label><br>
		<div>
		<div class="input_wrapper">
			<label for="email_message"><textarea type="text" id="email_message" name="email_message" value="" placeholder="email subject" rows="4" cols="50"></textarea></label><br>
		<div>
		<div class="input_wrapper">
			<label>Contact form ids</label><br>
		<div>
<?php

foreach( $forms as $k => $f) {
		echo '<div class="input_wrapper">';
			echo '<label for="contactformidd'.$k.'"><input type="checkbox" id="contactformidd'.$k.'" name="contactformidd[]" value="'.$f->id().'">'. $f->name().'</label><br>';
		echo '<div>';

	}
?>
	<div class="input_wrapper">
		<label for="email_sending_time">
			<select id="email_sending_time" name="email_sending_time">
			  <option value="once">once a day</option>
			  <option value="twice">twice a day</option>
			  <option value="every_night">every night</option>
			</select>
		</label><br>
	<div>
	<div class="input_wrapper">	
		<input type="submit" value="Save">
	<div>
	</form>
</div>