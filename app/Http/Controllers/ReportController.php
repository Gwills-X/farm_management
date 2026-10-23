<?php

namespace App\Http\Controllers;

use App\Models\AnimalType;
use App\Models\ExpenseCategory;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const FILTER_KEYS = [
        'animal_type_id', 'expense_category_id', 'status', 'health_status',
        'acquisition_type', 'payment_status', 'from', 'to',
    ];

    public function index(Request $request, ReportService $reports): Response|StreamedResponse
    {
        $type = (string) $request->query('type', 'animal_register');

        if (! array_key_exists($type, ReportService::DEFINITIONS)) {
            abort(404);
        }

        $validated = $request->validate([
            'animal_type_id' => ['nullable', 'integer', 'exists:animal_types,id'],
            'expense_category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'status' => ['nullable', Rule::in(['active', 'sold', 'deceased', 'archived'])],
            'health_status' => ['nullable', Rule::in(['healthy', 'monitoring', 'sick', 'recovered'])],
            'acquisition_type' => ['nullable', Rule::in(['purchased', 'born_on_farm', 'other'])],
            'payment_status' => ['nullable', Rule::in(['unpaid', 'partial', 'paid'])],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        // Only apply the filters this report actually uses.
        $allowed = ReportService::DEFINITIONS[$type]['filters'];
        $filters = array_filter(
            array_intersect_key($validated, array_flip($allowed)),
            fn ($value) => $value !== null && $value !== ''
        );

        $report = $reports->build($type, $filters);

        if ($request->query('export') === 'csv') {
            return $this->csv($report, $type);
        }

        return Inertia::render('Reports/Index', [
            'type' => $type,
            'definitions' => collect(ReportService::DEFINITIONS)
                ->map(fn ($def, $key) => ['key' => $key, 'label' => $def['label'], 'filters' => $def['filters']])
                ->values(),
            'filters' => array_merge(array_fill_keys(self::FILTER_KEYS, ''), $filters),
            'report' => $report,
            'animalTypes' => AnimalType::orderBy('name')->get(['id', 'name']),
            'categories' => ExpenseCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function csv(array $report, string $type): StreamedResponse
    {
        $filename = $type.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows naira and accents correctly
            fputcsv($out, array_column($report['columns'], 'label'));

            foreach ($report['rows'] as $row) {
                fputcsv($out, array_map(fn ($column) => $row[$column['key']] ?? '', $report['columns']));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
