<?php


namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;


class PdfService
{
    public function generatePdf($view, $data = [], $filename = 'document.pdf')
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view($view, $data)->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }
}
