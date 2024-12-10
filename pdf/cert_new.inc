<?php
function generate_pdf()
{
	$mpath = drupal_get_path('module', 'om_pssp');
	require($mpath.'/pdf/fpdf/fpdf.php');
	require($mpath.'/pdf/phpqrcode/qrlib.php');
	$user = \Drupal::currentUser();
	$x                 = $user->uid;
	$proposal_id       = arg(4);
	$query3 = \Drupal::database()->query("SELECT * FROM om_pssp_proposal WHERE approval_status=3 AND uid= :uid AND id=:proposal_id", array(
		':uid' => $user->uid,
		':proposal_id'=>$proposal_id
	));
	$data3 = $query3->fetchObject();
	if($data3){
	if($data3->uid != $x){
		\Drupal::messenger()->addError('Certificate is not available');
		return;
	}
	}
	$gender            = array(
		'salutation' => 'Mr. /Ms.',
		'gender' => 'He/She'
	);
	if ($data3->gender) {
		if ($data3->gender == 'M') {
			$gender = array(
				'salutation' => 'Mr.',
				'gender' => 'He'
			);
		} //$data3->gender == 'M'
		else {
			$gender = array(
				'salutation' => 'Ms.',
				'gender' => 'She'
			);
		}
	} //$data3->gender
	$pdf = new FPDF('L', 'mm', 'Letter');
	if (!$pdf) {
		echo "Error!";
	} //!$pdf
	$pdf->AddPage();
	$image_bg = $mpath . "/pdf/images/bg_cert.png";
	$pdf->Image($image_bg, 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
	$pdf->SetMargins(18, 1, 18);
	$path   = drupal_get_path('module', 'om_pssp');
	$pdf->Ln(50);
	$pdf->SetFont('Times', '', 14);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(240, 8, 'This is to certify that', '0', '1', 'C');
	$pdf->Ln(0);
	$pdf->SetFont('Times', 'I', 16);
	$pdf->SetTextColor(37, 22, 247);
	$contributor_name = WordWrap($data3->contributor_name,70);
	$pdf->MultiCell(240, 8, $data3->name_title . '. ' . utf8_decode($contributor_name), '0', 'C');
	$pdf->Ln(0);
	$pdf->SetFont('Times', '', 14);
	$title = WordWrap($data3->project_title,160);
	$university = WordWrap('from ' . $data3->university . ' has successfully contributed under the ', 160);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->MultiCell(240, 8, utf8_decode($university) . 'OpenModelica Power Systems Simulation Project.', '0','C');
	$pdf->Ln(0);
	$pdf->Cell(240, 8, 'He/She has created the following simulation', '0', '1', 'C');
	$pdf->SetTextColor(37, 22, 247);
	$pdf->SetFont('Times','I',16);
	$pdf->MultiCell(240, 8, utf8_decode($title), '0', 'C');
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Times','',14);
	$pdf->Cell(240, 8, 'using OpenModelica. The code is available at ', '0', '', 'C');
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Ln(8);
	$pdf->SetX(94);
	$pdf->SetFont('Times', 'I', 'U');
	$pdf->SetTextColor(37, 22, 247);
	$pdf->write(8, 'https://om.fossee.in/powersystems/pssp', 'https://om.fossee.in/powersystems/pssp');
	$pdf->Ln(0);
	$proposal_get_id = 0;
	$UniqueString    = "";
	$tempDir         = $path . "/pdf/temp_prcode/";
	$query           = \Drupal::database()->select('om_pssp_qr_code');
	$query->fields('om_pssp_qr_code');
	$query->condition('proposal_id', $proposal_id);
	$result          = $query->execute();
	$data            = $result->fetchObject();
	$DBString        = $data->qr_code;
	$proposal_get_id = $data->proposal_id;
	if ($DBString == "" || $DBString == "null") {
		$UniqueString = generateRandomString();
		$query        = "
				INSERT INTO om_pssp_qr_code
				(proposal_id,qr_code)
				VALUES
				(:proposal_id,:qr_code)
				";
		$args         = array(
			":proposal_id" => $proposal_id,
			":qr_code" => $UniqueString
		);
		$result       = \Drupal::database()->query($query, $args, $query);
	} //$DBString == "" || $DBString == "null"
	else {
		$UniqueString = $DBString;
	}
	$codeContents        = "https://om.fossee.in/powersystems/pssp/certificates/verify/" . $UniqueString;
	$fileName            = 'generated_qrcode.png';
	$pngAbsoluteFilePath = $tempDir . $fileName;
	$urlRelativeFilePath = $path . "/pdf/temp_prcode/" . $fileName;
	QRcode::png($codeContents, $pngAbsoluteFilePath);
	$pdf->SetY(80);
	$pdf->SetX(300);
	$pdf->Ln(30);
	$pdf->Image($pngAbsoluteFilePath, $pdf->GetX()+70 , $pdf->GetY() + 55, 24,24, 0);
	$pdf->SetFont('Times', '', 14);
	$pdf->Ln(48);
	$pdf->SetX(95);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(200, 8, $UniqueString, '0', '1' , 'L');
	$pdf->SetY(150);
	$pdf->SetX(800);
	$pdf->SetFont('Arial', 'I', 8);
	$pdf->SetTextColor(0, 0, 0);
	$filename = str_replace(' ', '-', $data3->contributor_name) . '-OpenModelica-PSSP-Certificate.pdf';
	$file     = $path . '/pdf/temp_certificate/' . $proposal_id . '_' . $filename;
	$pdf->Output($file, 'F');
	ob_clean();
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Type: application/pdf");
	header("Content-Disposition: attachment; filename=" . $filename);
	header("Content-Length: " . filesize($file));
	header("Content-Transfer-Encoding: binary");
	header("Expires: 0");
	header("Pragma: no-cache");
	flush();
	$fp = fopen($file, "r");
	while (!feof($fp)) {
		echo fread($fp, filesize($file));
		flush();
	} //!feof($fp)
	ob_end_flush();
	ob_clean();
	fclose($fp);
	unlink($file);
	//drupal_goto('flowsheeting-project/certificate');
	return;
}
function generateRandomString($length = 5)
{
	$characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString     = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	} //$i = 0; $i < $length; $i++
	return $randomString;
}