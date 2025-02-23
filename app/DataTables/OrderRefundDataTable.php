<?php

namespace App\DataTables;

use App\Models\OrderRefund;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class OrderRefundDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('action', function (OrderRefund $refund_request) {
                return view('order.refund-action', compact('refund_request'));
            })
            ->addColumn('order_id', function ($refund_request) {
                $order_refund_details = \App\Models\Order::order_detail($refund_request->order_id);
                $order_id_encrypted = \Illuminate\Support\Facades\Crypt::encrypt($refund_request->order_id);
                $html = '<div class="d-flex align-items-center">
                            <a href="' . route('refund-request.show', $order_id_encrypted) . '"
                               class="btn btn-primary btn-sm text-sm"
                               data-bs-toggle="tooltip" title="' . __('Invoice ID') . '">
                                <span class="btn-inner--icon"></span>
                                <span class="btn-inner--text">' . ($order_refund_details['order_id'] ?? '') . '</span>
                            </a>
                        </div>';

                return $html;
            })
            ->addColumn('created_at', function ($refund_request) {
                return \Carbon\Carbon::parse($refund_request['created_at'])->format('Y-m-d');
            })
            ->addColumn('refund_status', function ($refund_request) {
                $badge_class = 'bg-light-success';
                if ($refund_request->refund_status == 'Cancel') {
                    $badge_class = 'bg-light-danger';
                } elseif ($refund_request->refund_status == 'Processing') {
                    $badge_class = 'bg-light-info';
                } elseif ($refund_request->refund_status == 'Refunded') {
                    $badge_class = 'bg-light-warning';
                }
                return '<span class="badge badge-80 rounded p-2 f-w-600 ' . $badge_class . '">'
                       . $refund_request['refund_status'] . '</span>';
            })
            ->filterColumn('order_id', function ($query, $keyword) {
                $query->whereHas('order', function ($subQuery) use ($keyword) {
                    $subQuery->where('product_order_id', 'like', "%$keyword%");
                });
            })
            ->rawColumns(['action', 'order_id', 'created_at', 'refund_status']);
        return $dataTable;
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(OrderRefund $model): QueryBuilder
    {
        return $model->newQuery()->where('theme_id', APP_THEME())->where('store_id', getCurrentStore());
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('order-refunds-table')
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
        ];

        $bulkdeleteButtonConfig = [];
        if (module_is_active('BulkDelete')) {
            $bulkdeleteButtonConfig = bulkDeleteForm('order-refunds','order-refunds-table');
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
            Column::make('id')->searchable(false)->visible(false)->printable(false),
            Column::make('order_id')->title(__('Order Id')),
            Column::make('created_at')->title(__('Refund Request Date')),
            Column::make('refund_status')->title(__('Refund Request Status'))->addClass('text-capitalize'),
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
        return 'OrderRefund_' . date('YmdHis');
    }
}
