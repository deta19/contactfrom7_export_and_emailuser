<div class="wrapper">
	<?php
		if( !empty( $_GET['success'] ) && $_GET['success'] == 0 ) {
			echo '<div class="success error">submited with success</div>';
		} else if( !empty( $_GET['success'] ) && $_GET['success'] == 1 ) {
			echo '<div class="success error">can not be submited</div>';

		}

	?>
<table>
	<tr>
	    <th>Contact form</th>
	    <th>Created at</th>
	    <th>Action</th>
	</tr>

	<?php
		foreach ($all_emails_to_send as $key => $all_email_to_send) {
		// var_dump($all_email_to_send);
	?>
	<tr>
		<form id="contacform7_forms" action="<?php echo admin_url( 'admin-post.php' );?>" method="POST">
			<input type="hidden" name="action" value="send_selected_email_cstm">
			<input type="hidden" name="mail_send_id" value="<?php echo $all_email_to_send->contact_from_id; ?>">
		 	<?php wp_nonce_field( 'send_selected_email_cstm', 'sendcf7id' ); ?>
		<td>
			<div class="input_wrapper">
				<label><?php echo $cf7_arranged_arr[$all_email_to_send->contact_from_id]; ?></label><br>
			<div>
		</td>
		<td>
			<div class="input_wrapper">	
				<?php echo $all_email_to_send->date_create_at; ?>
			<div>
		</td>
		<td>
			<div class="input_wrapper">	
				<input type="submit" value="send">
			<div>
		</td>
		</form>
	</tr>
	<?php
		}
	?>
</table>