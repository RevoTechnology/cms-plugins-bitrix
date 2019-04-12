<?php
/**
 * Created in Heliard.
 * User: gvammer gvammer@rambler.ru
 * Date: 2019-04-13
 * Time: 01:08
 */

namespace Revo;

require_once(__DIR__ . '/../lib/fpdf/fpdf.php');
require_once(__DIR__ . '/../lib/fpdf/fpdf_to_html.php');
require_once(__DIR__ . '/../lib/fpdf/pdf_mc_table.php');

class Documents
{
    public static function convertHtmlToPdf($html, $filePath)
    {
        $pdf = new \PDF_HTML();

        $pdf->SetFont('Arial','',12);
        $pdf->AddPage();
        $pdf->WriteHTML('<html><head></head><body>'.$html.'</body></html>');
        $pdf->Output('F', $_SERVER['DOCUMENT_ROOT'] . $filePath);
    }
}