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
    protected $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function exportPdf(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            $purchaseOrders = PurchaseOrder::with(['supplier', 'inputOrders.input'])
                ->whereBetween('PurchaseOrderDate', [$startDate, $endDate])
                ->orderBy('PurchaseOrderDate', 'desc')
                ->get();

            if ($purchaseOrders->isEmpty()) {
                Log::warning('No hay órdenes en el rango de fechas.');
                return response()->json(['error' => 'No hay órdenes en el rango especificado.'], 404);
            }

            $totalAmount = $purchaseOrders->sum('PurchaseTotal');

            $data = [
                'purchaseOrders' => $purchaseOrders,
                'totalAmount' => $totalAmount,
                'startDate' => $startDate->format('d/m/Y'),
                'endDate' => $endDate->format('d/m/Y'),
                'generatedAt' => now()->format('d/m/Y H:i'),
            ];

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
