<?php

namespace App\DataTables;

use App\Models\PlanUserCoupon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PlanCouponDetailDataTable extends DataTable
{
    protected $planCouponId;

    /**
     * Set the PlanCoupon ID for filtering.
     *
     * @param int $planCouponId
     */
    public function setPlanCouponId(int $planCouponId)
    {
        $this->planCouponId = $planCouponId;
    }

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {

        return datatables()
        ->eloquent($query)
        ->addColumn('title', function ($row) {
            return $row->coupon_detail->name ?? null;
        })
        ->addColumn('order_id', function ($row) {
            return '<div class="btn btn-primary btn-sm text-sm" data-toggle="tooltip" ">
                            <span class="btn-inner--icon"></span>
                            <span class="btn-inner--text">#'. $row->order . '</span>
                        </div>';
        })
        ->addColumn('amount', function ($row) {
            if (isset($row->order_detail->price)) {
                return $row->order_detail->price;
            } elseif (module_is_active('Campaigns')) {
                $campaignsData = \Workdo\Campaigns\app\Models\Campaigns::find($row->order);
                if ($campaignsData) {
                    return $campaignsData->total_cost;
                }
            }
            return $row->order_detail->price ?? '-';
        })
        ->editColumn('created_at', function ($row) {
            return isset($row->created_at) ? $row->created_at->format('Y-m-d H:i:s') : '-';
        })
        ->filterColumn('title', function ($query, $keyword) {
            // Assuming `permissions` is a relationship, adjust as needed
            $query->whereHas('coupon_detail', function ($subQuery) use ($keyword) {
                $subQuery->where('name', 'like', "%$keyword%");
            });
        })
        ->filterColumn('amount', function ($query, $keyword) {
            // Assuming `permissions` is a relationship, adjust as needed
            $query->whereHas('order_detail', function ($subQuery) use ($keyword) {
                $subQuery->where('price', 'like', "%$keyword%");
            });
        })
        ->filterColumn('order_id', function ($query, $keyword) {
            // Assuming `permissions` is a relationship, adjust as needed
            $query->where('order', 'like', "%$keyword%");
        })
        ->rawColumns(['order_id']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(PlanUserCoupon $model): QueryBuilder
    {
    return $model->newQuery()->where('coupon_id', $this->planCouponId);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('plan-coupon-detail-table')
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
                [
                    'extend' => 'csv',
                    'text' => '<i class="fas fa-file-csv"></i> ' . __('CSV'),
                    'className' => 'btn btn-light text-primary dropdown-item export-btn',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
                [
                    'extend' => 'excel',
                    'text' => '<i class="fas fa-file-excel"></i> ' . __('Excel'),
                    'className' => 'btn btn-light text-primary dropdown-item export-btn',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
            ],
        ];

        $bulkdeleteButtonConfig = [];
        if (module_is_active('BulkDelete')) {
            $bulkdeleteButtonConfig = bulkDeleteForm('plan-coupon-detail','plan-coupon-detail-table');
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
                'export' => __('Export'),
                'print' => __('Print'),
                'reset' => __('Reset'),
                'reload' => __('Reload'),
                'excel' => __('Excel'),
                'csv' => __('CSV'),
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
            Column::make('title')->title(__('Title')),
            Column::make('order_id')->title(__('Order ID')),
            Column::make('amount')->title(__('Amount')),
            Column::make('created_at')->title(__('Date')),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Coupon_detail_' . date('YmdHis');
    }
}
