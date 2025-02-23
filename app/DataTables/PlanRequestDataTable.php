<?php

namespace App\DataTables;

use App\Models\PlanRequest;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PlanRequestDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
        ->addIndexColumn()
        ->editColumn('user_id', function ($prequest) {
            return $prequest->user->name;
        })
        ->editColumn('plan_id', function ($prequest) {
            return $prequest->plan->name;
        })
        ->addColumn('max_products', function ($prequest) {
            return ($prequest->plan->max_products == '-1') ? __('Unlimited') : $prequest->plan->max_products .' Products';
        })
        ->addColumn('max_stores', function ($prequest) {
            return ($prequest->duration == 'Month') ? __('One Month') : (($prequest->duration == 'Year') ? __('One Year') : ($prequest->duration ?? '-'));
        })
        ->editColumn('created_at', function ($prequest) {
            return isset($prequest) ? $prequest->created_at->format('Y-m-d H:i:s') : '-';
        })
        ->addColumn('action', function ($prequest) {
            return view('plan_request.action', compact('prequest'));
        })
        ->filterColumn('user_id', function ($query, $keyword) {
            // Assuming `permissions` is a relationship, adjust as needed
            $query->whereHas('user', function ($subQuery) use ($keyword) {
                $subQuery->where('name', 'like', "%$keyword%");
            });
        })
        ->filterColumn('plan_id', function ($query, $keyword) {
            // Assuming `permissions` is a relationship, adjust as needed
            $query->whereHas('plan', function ($subQuery) use ($keyword) {
                $subQuery->where('name', 'like', "%$keyword%");
            });
        })
        ->filterColumn('max_products', function ($query, $keyword) {
            // Assuming `permissions` is a relationship, adjust as needed
            $query->whereHas('plan', function ($subQuery) use ($keyword) {
                if (stripos('Unlimited', $keyword) !== false) {
                    // Filter for guest customers
                    $subQuery->where('max_products', '-1');
                } else {
                    $subQuery->where('max_products', $keyword);
                }
            });
        })
        ->filterColumn('max_stores', function ($query, $keyword) {
            // Assuming `permissions` is a relationship, adjust as needed
            $query->whereHas('plan', function ($subQuery) use ($keyword) {
                if (stripos('Unlimited', $keyword) !== false) {
                    // Filter for guest customers
                    $subQuery->where('max_stores', '-1');
                } else {
                    $subQuery->where('max_stores', $keyword);
                }
            });
        })
        ->rawColumns(['user_id','action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(PlanRequest $model): QueryBuilder
    {
        return $model->newQuery()->with(['user','plan']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('plan-request-table')
            ->columns(array_merge(array_slice($this->getColumns(), 0, 1),bulkDeleteCloneCheckboxColumn(),array_slice($this->getColumns(), 1)))
            ->minifiedAjax()
            ->orderBy(0)
            ->language([
                "paginate" => [
                    "next" => '<i class="ti ti-chevron-right"></i>',
                    "previous" => '<i class="ti ti-chevron-left"></i>'
                ],
                'lengthMenu' => "_MENU_" . __('Entries Per Page'),
                "searchPlaceholder" => __('Search...'),
                "search" => "",
                "info" => __("Showing")." _START_ ".__("to"). " _END_ ".__("of")." _TOTAL_ ".__("entries")
            ])
            ->initComplete('function() {
                        var table = this;

                        var searchInput = $(\'#\'+table.api().table().container().id+\' label input[type="search"]\');
                        searchInput.removeClass(\'form-control form-control-sm\');
                        searchInput.addClass(\'dataTable-input\');
                        var select = $(table.api().table().container()).find(".dataTables_length select").removeClass(\'custom-select custom-select-sm form-control form-control-sm\').addClass(\'dataTable-selector\');
                    }');
        $exportButtonConfig = [
            'extend' => 'collection',
            'className' => 'btn btn-light-secondary',
            'text' => '<i class="ti ti-download" data-bs-toggle="tooltip" title="'.__("Export").'" data-bs-original-title="'.__("Export").'"></i>',
            'buttons' => [
                [
                    'extend' => 'print',
                    'text' => '<i class="fas fa-print"></i> ' . __('Print'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
            ],
        ];

        $bulkdeleteButtonConfig = [];
        if (module_is_active('BulkDelete')) {
            $bulkdeleteButtonConfig = bulkDeleteForm('plan-request','plan-request-table');
        }

        $buttonsConfig = array_merge([
            $bulkdeleteButtonConfig,
            $exportButtonConfig,
            [
                'text' => '<i class="ti ti-arrow-back-up" data-bs-toggle="tooltip" title="'.__("Reset").'" data-bs-original-title="'.__("Reset").'"></i>',
                'extend' => 'reset',
                'className' => 'btn btn-light-info',
            ],
            [
                'text' => '<i class="ti ti-refresh" data-bs-toggle="tooltip" title="'.__("Reload").'" data-bs-original-title="'.__("Reload").'"></i>',
                'extend' => 'reload',
                'className' => 'btn btn-light-warning',
            ],
        ]);

        $dataTable->parameters([
            "dom" =>  "
        <'dataTable-top'<'dataTable-dropdown page-dropdown'l><'dataTable-botton table-btn dataTable-search tb-search  d-flex justify-content-end gap-1'Bf>>
        <'dataTable-container'<'col-sm-12'tr>>
        <'dataTable-bottom row'<'col-5'i><'col-7'p>>",
            'buttons' => $buttonsConfig,
            "drawCallback" => 'function( settings ) {
                var tooltipTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=tooltip]")
                  );
                  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                  });
                  var popoverTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=popover]")
                  );
                  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                  });
                  var toastElList = [].slice.call(document.querySelectorAll(".toast"));
                  var toastList = toastElList.map(function (toastEl) {
                    return new bootstrap.Toast(toastEl);
                  });
            }'
        ]);

        $dataTable->language([
            'buttons' => [
                'create' => __('Create'),
                'print' => __('Print'),
                'reset' => __('Reset'),
                'reload' => __('Reload'),
            ]
        ]);

        return $dataTable;
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('user_id')->title(__('Name')),
            Column::make('plan_id')->title(__('Plan Name')),
            Column::make('max_products')->title(__('Max Products')),
            Column::make('max_stores')->title(__('Max Stores')),
            Column::make('duration')->title(__('Duration')),
            Column::make('created_at')->title(__('Created at')),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center')->title(__('Action')),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'PlanRequest_' . date('YmdHis');
    }
}
