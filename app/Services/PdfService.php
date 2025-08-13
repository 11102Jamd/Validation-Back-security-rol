<?php


namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;


class PdfService
{

    /**
     * Genera un documento PDF a partir de una vista de Laravel.
     *
     * @param string $view      Nombre de la vista Blade que se usará para el contenido.
     * @param array  $data      Datos que se pasarán a la vista.
     * @param string $filename  Nombre del archivo PDF (opcional, por defecto 'document.pdf').
     *
     * @return \Dompdf\Dompdf  Instancia de Dompdf con el PDF renderizado.
     */
    public function generatePdf($view, $data = [], $filename = 'document.pdf')
    {
        //configuracion de opciones para Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true); // Permite cargar recursos externos
        $options->set('defaultFont', 'Arial'); // Define la fuente por defecto

        //Se crea la instancia de Dompdf con opciones personalizadas
        $dompdf = new Dompdf($options);

        // Carga erl contenido HTML de la vista blade
        $dompdf->loadHtml(view($view, $data)->render());

        // Definir el tamaño de la Hoja y orientacion
        $dompdf->setPaper('A4', 'portrait');

        // Renderiza el PDF
        $dompdf->render();

        // Devuelve el objeto creado a partir de la instancia
        return $dompdf;
    }
}
