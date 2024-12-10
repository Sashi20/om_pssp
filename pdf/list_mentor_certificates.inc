<?php
function _list_pssp_custom_certificates()
{
	$user = \Drupal::currentUser();
	$query_id = \Drupal::database()->query("SELECT id FROM om_pssp_proposal WHERE approval_status=3");
	$exist_id = $query_id->fetchObject();
	if ($exist_id){
	if ($exist_id->id) {
		if ($exist_id->id < 1) {
			\Drupal::messenger()->addStatus('<strong>You need to propose a flowsheet <a href="http://dwsim.fossee.in/flowsheeting-project/proposal">Flowsheet Proposal</a></strong> or if you have already proposed then your flowsheet is under reviewing process');
			return '';
		} //$exist_id->id < 3
		else {
			$search_rows = array();
			global $output;
			$output = '';
			$query3 = \Drupal::database()->query("SELECT id,project_guide_name,project_guide_university,project_title FROM 
om_pssp_proposal WHERE project_guide_name != '' AND project_guide_university != '' AND approval_status=3");
			$i = 1;
			while ($search_data3 = $query3->fetchObject()) {
					// @FIXME
// l() expects a Url object, created from a route name or external URI.
// $search_rows[] = array(
// 						$i,
// 						$search_data3->project_title,
// 						$search_data3->project_guide_name,
// 						l('Download Certificate', 'powersystems/pssp/certificates-custom/pdf/' . $search_data3->id)
// 					);

					$i++;
				 //$search_data3->id
			} //$search_data3 = $query3->fetchObject()
			if ($search_rows) {
				$search_header = array(
					'No',
					'Project Title',
					'Project Guide Name',
					'Download Certificates'
				);
				// @FIXME
// theme() has been renamed to _theme() and should NEVER be called directly.
// Calling _theme() directly can alter the expected output and potentially
// introduce security issues (see https://www.drupal.org/node/2195739). You
// should use renderable arrays instead.
// 
// 
// @see https://www.drupal.org/node/2195739
// $output = theme('table', array(
// 					'header' => $search_header,
// 					'rows' => $search_rows
// 				));

				return $output;
			} //$search_rows
			else {
				echo ("Error");
				return '';
			}
		}
		}
	} //$exist_id->id
	else {
		\Drupal::messenger()->addStatus('<strong>You need to propose a flowsheet <a href="http://dwsim.fossee.in/flowsheeting-project/proposal">Flowsheet Proposal</a></strong> or if you have already proposed then your flowsheet is under reviewing process');
		$page_content = "<span style='color:red;'> No certificate available </span>";
		return $page_content;
	}
}
