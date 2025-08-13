<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Manufacturing\Manufacturing;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ManufacturingPdfController extends Controller
{
    /**
     * Propiedad del servicio para generar un pdf
     *
     * @var PdfService
     */
    protected $pdfService;


    /**
     * Declaramos un constructor que Inyecta el servicio de PDF al controlador
     *
     * @param PdfService $pdfService
     */
    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Genera y exporta un PDF de fabricación dentro de un rango de fechas.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        try {
            // Validar que las fechas estén presentes y que el rango sea válido
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            // Convertir fechas al inicio y final del día para abarcar todo el rango
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            // Consultar fabricaciones con sus productos e insumos relacionados
            $manufacturing = Manufacturing::with(['product', 'recipes.input'])
                ->whereBetween('manufacturingDate', [$startDate, $endDate])
                ->orderBy('manufacturingDate', 'desc')
                ->get();

            // Validar si no hay resultados en el rango
            if ($manufacturing->isEmpty()) {
                Log::warning('No hay fabricaciones en el rango de fechas.');
                return response()->json(['error' => 'No hay órdenes en el rango especificado.'], 404);
            }

            // Calcular el costo total de producción en el rango
            $totalAmount = $manufacturing->sum('TotalCostProduction');

            // Preparar datos para la vista del PDF
            $data = [
                'manufacturings' => $manufacturing,
                'totalAmount' => $totalAmount,
                'startDate' => $startDate->format('d/m/Y'),
                'endDate' => $endDate->format('d/m/Y'),
                'generatedAt' => now()->format('d/m/Y H:i'),
            ];

            // Generar el PDF usando el servicio
            $pdf = $this->pdfService->generatePdf('pdf.manufacturings', $data);

            // Devuelve el PDF como una respuesta descargable
            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="reporte-fabricaciones.pdf"')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
                
        } catch (\Throwable $th) {
            Log::error('Error al generar PDF:', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error interno al generar el PDF.',
                'details' => env('APP_DEBUG') ? $th->getMessage() : null,
            ], 500);
        }
    }
}
