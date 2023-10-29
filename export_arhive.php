<?php
require 'vendor/autoload.php';



ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Export_arhive {
	
	public function generate_files( $id_form ) {
	
		if( class_exists('Advanced_Cf7_Db_Admin') ) {
			
			global $wpdb;

			// $id_form = 1462;
			// $id_form = 10;
			$id_elem_selectat = implode( ',', array() );
			// vsz_cf7_export_to_excel($fid, $ids_export);
			
			$result = array();


			$fid = intval($id_form);

// var_dump($fid);
// die;
			if( empty( $fid ) ){
		    	return 'You do not have the permission to export the data';
		    }

			//Get form id related contact form object
			$obj_form = vsz_cf7_get_the_form_list($fid);

		    $fields = vsz_cf7_get_db_fields($fid);



		    $fields1 = vsz_field_type_info($fid);


		


			//get current form title
			$form_title = esc_html($obj_form[0]->title());
			$timeStamp = date('Ymdhis');
			$form_title = preg_replace('/\s+/', '_', $form_title);
			$docName = $form_title."-".$timeStamp;

			//Get export data
			$data = create_export_query($fid, $id_elem_selectat, 'data_id desc');


			
			if(!empty($data)){

				$data_sorted = wp_unslash(vsz_cf7_sortdata($data));
				$arrHeader = array_values(array_map('sanitize_text_field',$fields));

				// if( VSZ_CF7_PHPSPREADSHEET_CHECK == true){

					$spreadsheet = new Spreadsheet();
					$sheet = $spreadsheet->getActiveSheet();

					//First we will set header in excel file
					$col = 1;
					$row = 1;
					foreach($arrHeader as $colName){

						$sheet->setCellValueByColumnAndRow($col, $row, $colName);
						$col++;
					}

					$row = 2;
					foreach ($data_sorted as $k => $v){

						//Define column index here
						$col=1;
						//Get column order wise value here
						foreach ($fields as $k2 => $v2){

							$colVal = (isset($v[$k2]) ? html_entity_decode($v[$k2]) : '');
							$sheet->setCellValueByColumnAndRow($col, $row, $colVal);
							$col++;
						}
						//Consider new row for each entry here
						$row++;
					}

					$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xls");

					// header('Content-Type: application/vnd.ms-excel');
			        // header('Content-Disposition: attachment; filename="'. urlencode($docName.'.xls').'"');
					$writer->save( __DIR__ .'/export/'.$docName.'.xls' );
					// exit;
				// }
			

				
				if( file_exists( __DIR__ .'/export/'.$docName.'.xls' ) ) {
					$zip = new ZipArchive();
					$filename =  __DIR__ .'/export/'.$docName.'.zip';
					
					if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
					    exit("cannot open <$filename>\n");
					}
					
					$zip->addFile( __DIR__ .'/export/'.$docName.'.xls', $docName.'.xls' );
					// echo "numfiles: " . $zip->numFiles . "\n";
					// echo "status:" . $zip->status . "\n";
					$zip->close();
					
					$result['excel_file'] = $docName.'.xls';
					$result['arhive_file'] =  $docName.'.zip';

				}
			
				return $result;
			
			}

		}	
		
		
		
	}
	
	/*
	*	$email_address = email address to whom you want to send files
	*	$file = array with path to file that you nwa t to attach to email
	*/
	public function send_email( $email_address, $file, $subject, $message ) {
		 $headers = array(
			    'MIME-Version: 1.0',
			    'Content-type: text/html; charset=utf-8',
			    'From: fred@sender.com',
			);
		$sent = wp_mail($email_address, $subject, strip_tags($message), $headers, $file);
    
		if($sent) {
		  //message sent!
		  return true;    
		}
		else  {
		  //message wasn't sent       
		  return false;    
		}
	}
	
}