<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Services\PdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseOrderPdfController extends Controller
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
     * Genera y exporta un PDF de compras dentro de un rango de fechas.
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

            // Consultar compras con sus insumos relacionados
            $purchaseOrders = PurchaseOrder::with(['supplier', 'inputOrders.input'])
                ->whereBetween('PurchaseOrderDate', [$startDate, $endDate])
                ->orderBy('PurchaseOrderDate', 'desc')
                ->get();

            // Validar si no hay resultados en el rango
            if ($purchaseOrders->isEmpty()) {
                Log::warning('No hay órdenes en el rango de fechas.');
                return response()->json(['error' => 'No hay órdenes en el rango especificado.'], 404);
            }

            // Calcular el costo total de compras en el rango
            $totalAmount = $purchaseOrders->sum('PurchaseTotal');

            // Preparar datos para la vista del PDF
            $data = [
                'purchaseOrders' => $purchaseOrders,
                'totalAmount' => $totalAmount,
                'startDate' => $startDate->format('d/m/Y'),
                'endDate' => $endDate->format('d/m/Y'),
                'generatedAt' => now()->format('d/m/Y H:i'),
            ];

            // Generar el PDF usando el servicio
            $pdf = $this->pdfService->generatePdf('pdf.purchase-orders', $data);

            // Devuelve el PDF como una respuesta descargable
            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="reporte-ordenes.pdf"')
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
