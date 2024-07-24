<?php
/* Template Name: Certificate */

require_once(__DIR__ . '/utils/pdf-viewer/pdf.php');
require_once(__DIR__ . '/utils/Certificate.php');

$certificateID = get_query_var('certificate_id');

displayCertificateIfValid($certificateID);

function displayCertificateIfValid($certificateID) {
    try {
        displayCertificate($certificateID);
    } catch(Exception $e) {
        echo $e->getMessage();
    }
}

function displayCertificate($certificateID) {
    $certificateQueryResult = queryCertificate($certificateID);
    $certificate = returnCertificateIfValid($certificateQueryResult);
    tryToDisplayCertificate($certificate);
}

function returnCertificateIfValid($queryResult) {
    $certicatesFound = count($queryResult);
    if ($certicatesFound == 0) {
        throw new Exception('');
    } else if ($certicatesFound > 1) {
        throw new Exception('Erro ao recuperar o certificado, contate gabriel@bookinvideo.com');
    } else {
        return $queryResult[0];
    }
}

function queryCertificate($certificateID) {
    global $wpdb;
    $tableName = $wpdb->prefix . 'conclusion_certificates';
    $query = $wpdb->prepare(
        "SELECT `*` FROM `$tableName` WHERE `id` = %s",
        array($certificateID)
    );
    return $wpdb->get_results($query);
}

function tryToDisplayCertificate($certificate) {
    $course = new \AppCertificate\Certificate(
        $certificate->userId,
        $certificate->courseSlug,
        array(
            'startDate' => $certificate->startDate,
            'endDate' => $certificate->endDate,
        )
    );
    
    $pdf = new PDF('L');
    $pdf->AddFont('Inter', '', 'Inter-Bold.php');
    
    $pdf->addPage();

    $imagePath = __DIR__ . '/assets/images/certificate.png';
    $pdf->Image($imagePath, 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
    
    $pdf->SetTitle($certificate->id);
    
    $pdf->SetFont('Inter', '', 32);
    $pdf->SetXY(75, 71);
    $pdf->MultiCell(100, 12, $course->getCourseName());
    
    $pdf->SetXY(189, 71);
    $pdf->MultiCell(100, 12, $course->totalHours() . ' horas');
    
    $pdf->SetXY(80, 119);
    $pdf->MultiCell(215, 12, $course->getStudentFullName());
    
    $pdf->SetFont('Inter', '', 16);
    $pdf->SetXY(189, 86);
    $pdf->SetTextColor(96, 97, 99);
    $pdf->MultiCell(
        100, 
        12, 
        $course->getStartDate() . " - " . $course->getEndDate()
    );
    
    $pdf->SetFont('Inter', '', 12);
    $pdf->SetXY(189, 139);
    $pdf->MultiCell(107, 12, "bookinvideo.com/certificate/{$certificate->id}");
    
    $pdf->Output();
}
?>