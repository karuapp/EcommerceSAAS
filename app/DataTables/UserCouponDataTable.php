<?php

namespace App\DataTables;

use App\Models\UserCoupon;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UserCouponDataTable extends DataTable
{
    protected $couponId;

    /**
     * Set the PlanCoupon ID for filtering.
     *
     * @param int $couponId
     */
    public function setCouponId(int $couponId)
    {
        $this->couponId = $couponId;
    }
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
        ->addIndexColumn()
        ->editColumn('coupon_name', function (UserCoupon $coupon) {
            return $coupon->CouponData ? $coupon->CouponData->coupon_name : '';
        })
        ->editColumn('product_order_id', function (UserCoupon $coupon) {
            return $coupon->OrderData ? ('#'.$coupon->OrderData->product_order_id) : '';
        })
        ->editColumn('product_order_id', function (UserCoupon $coupon) {
            return '<div class="btn btn-primary btn-sm text-sm" data-bs-toggle="tooltip" title="' . __('Invoice ID') . '">
                        <span class="btn-inner--icon"></span>
                        <span class="btn-inner--text">#'. $coupon->OrderData->product_order_id . '</span>
                    </div>';
        })
        ->filterColumn('coupon_name', function ($query, $keyword) {
            // Assuming `permissions` is a relationship, adjust as needed
            $query->whereHas('CouponData', function ($subQuery) use ($keyword) {
                $subQuery->where('coupon_name', 'like', "%$keyword%");
            });
        })
        ->filterColumn('product_order_id', function ($query, $keyword) {
            // Assuming `permissions` is a relationship, adjust as needed
            $query->whereHas('OrderData', function ($subQuery) use ($keyword) {
                $subQuery->where('product_order_id', 'like', "%$keyword%");
            });
        })
        ->orderColumn('coupon_name', function ($query, $direction) {
            // Assuming `permissions` is a relationship, adjust as needed
            $query->whereHas('CouponData', function ($subQuery) use ($direction) {
                $subQuery->orderBy('coupon_name', $direction);
            });
        })
        ->orderColumn('product_order_id', function ($query, $direction) {
            // Assuming `permissions` is a relationship, adjust as needed
            $query->whereHas('OrderData', function ($subQuery) use ($direction) {
                $subQuery->orderBy('product_order_id', $direction);
            });
        })
        ->rawColumns(['coupon_name','product_order_id','amount', 'date_used']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(UserCoupon $model): QueryBuilder
    {
        return $model->newQuery()->where('coupon_id', $this->couponId);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('usercoupon-table')
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

        $exportButtonConfig = [];
        // $exportButtonConfig = [
        //     'extend' => 'collection',
        //     'className' => 'btn btn-light-secondary me-1 dropdown-toggle',
        //     'text' => '<i class="ti ti-download"></i> ' . __('Export'),
        //     'buttons' => [
        //         [
        //             'extend' => 'print',
        //             'text' => '<i class="fas fa-print"></i> ' . __('Print'),
        //             'className' => 'btn btn-light text-primary dropdown-item',
        //             'exportOptions' => ['columns' => [0, 1, 3]],
        //         ],
        //     ],
        // ];

        $bulkdeleteButtonConfig = [];
        if (module_is_active('BulkDelete')) {
            $bulkdeleteButtonConfig = bulkDeleteForm('user-coupon','usercoupon-table');
        }

        $buttonsConfig = array_merge([
            $exportButtonConfig,
            $bulkdeleteButtonConfig,
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
            Column::make('coupon_name')->title(__('Name')),
            Column::make('product_order_id')->title(__('Order ID')),
            Column::make('amount')->title(__('Amount')),
            Column::make('date_used')->title(__('Date')),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'UserCoupon_' . date('YmdHis');
    }
}
