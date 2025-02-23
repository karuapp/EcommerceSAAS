<?php

namespace App\DataTables;

use App\Models\PlanOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PlanOrderDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $userOrders = [];
        if (auth()->user() && auth()->user()->type == 'super admin') {
            $userOrders = PlanOrder::select('*')
            ->whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
            ->from('plan_orders')
            ->groupBy('user_id');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        }

        return datatables()
            ->eloquent($query)
            ->editColumn('order_id', function ($order) {
                return '#'. ($order->order_id ?? null);
            })
            ->editColumn('order_id', function ($order) {
                return '  <div " class="btn btn-primary btn-sm text-sm" data-bs-toggle="tooltip" title="' . __('Order ID') . '">
                                <span class="btn-inner--icon"></span>
                                <span class="btn-inner--text">#'. $order->order_id . '</span>
                            </div>
                       ';
            })
            ->editColumn('created_at', function ($order) {
                return isset($order->created_at) ? $order->created_at->format('Y-m-d H:i:s') : '-';
            })
            ->editColumn('user_name', function ($order) {

                return $order->user_name ?? '-';
            })
            ->editColumn('plan_name', function ($order) {
                return $order->plan_name ?? '-';
            })
            ->editColumn('price', function ($order) {
                return GetCurrency() . $order->price;
            })
            ->editColumn('payment_status', function ($order) {
                return view('plans.payment_status', compact('order'));
            })
            ->editColumn('total_coupon_used', function ($order) {
                return isset($order->total_coupon_used->coupon_detail->code)
                    ? $order->total_coupon_used->coupon_detail->code
                    : '-';
            })
            ->editColumn('receipt', function ($order) {
                return view('plans.receipt', compact('order'));
            })
            ->addColumn('action', function ($order) use ($userOrders) {
                $user = User::find($order->user_id);
                return view('plans.action', compact('order','user','userOrders'));
            })
            ->rawColumns(['order_id','created_at','user_name','plan_name','price','payment_status','total_coupon_used','receipt','action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(PlanOrder $model): QueryBuilder
    {
        $query = $model->newQuery()
        ->with(['total_coupon_used.coupon_detail']) // Eager-load the total_coupon_used relationship
        ->select(['plan_orders.*', 'users.name as user_name'])
        ->join('users', 'plan_orders.user_id', '=', 'users.id');

        if (auth()->user() && auth()->user()->type != 'super admin') {
            $query->where('users.id', '=', auth()->user()->id);
        }
        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('plan-order-table')
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
                    'className' => 'btn btn-light text-primary dropdown-order',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
            ],
        ];

        $bulkdeleteButtonConfig = [];
        if (module_is_active('BulkDelete')) {
            $bulkdeleteButtonConfig = bulkDeleteForm('plan-order','plan-order-table');
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
        $actions = [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('order_id')->title(__('Order Id')),
            Column::make('created_at')->title(__('Date')),
            Column::make('user_name')->title(__('User Name'))->name('users.name'),
            Column::make('plan_name')->title(__('Plan Name')),
            Column::make('price')->title(__('Price')),
            Column::make('payment_type')->title(__('Payment Type')),
            Column::make('payment_status')->title(__('Status'))->addClass('text-capitalize'),
            Column::make('total_coupon_used')
            ->title(__('Coupon'))
            ->name('total_coupon_used.coupon_detail.code') // Use the relationship name
            ->searchable(true)
            ->orderable(true),
            Column::make('receipt')->title(__('Invoice'))
        ];

        if (auth()->user() && auth()->user()->type == 'super admin') {
            $actions[] = Column::computed('action')
            ->exportable(false)
            ->printable(false)
            ->width(60)
            ->addClass('text-center')->title(__('Action'));
        }


        return $actions;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'PlanOrder_' . date('YmdHis');
    }
}
