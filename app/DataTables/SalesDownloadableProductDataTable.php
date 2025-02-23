<?php

namespace App\DataTables;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class SalesDownloadableProductDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
        ->addColumn('order_id', function ($order) {
            return '<a href="' . route('order.view', \Illuminate\Support\Facades\Crypt::encrypt($order->id)) . '" class="btn btn-primary btn-sm text-sm" data-bs-toggle="tooltip" title="' . __('Invoice ID') . '"><span class="btn-inner--text">#' . $order->product_order_id . '</span></a>';
        })
        ->editColumn('product_name', function ($order) {
            $products = json_decode($order->product_json, true);
            $productNames = [];

            foreach ($products as $product) {
                $productModel = Product::find($product['product_id']);
                $variantModel = ProductVariant::find($product['variant_id']);

                if ($productModel) {
                    $productNames[] = $productModel->name .  ($variantModel ? (' (' .$variantModel->variant. ')') : '') ;
                }
            }

            return implode('<br>', $productNames);
        })
        ->editColumn('created_at', function ($order) {
            return $order->created_at->toDateTimeString();
        })
        ->addColumn('customer', function ($order) {
            if ($order->is_guest == 1) {
                return __('Guest');
            } elseif ($order->customer_id != 0) {
                return !empty($order->CustomerData) ? ($order->CustomerData->first_name .' '. $order->CustomerData->last_name) : '';
            } else {
                return __('Walk-in-customer');
            }
        })
        ->addColumn('attachment', function ($order) {
            $output = '';
            $products = json_decode($order->product_json, true);
            foreach ($products as $product) {
                $variant = ProductVariant::where('id', $product['variant_id'])->first();
                $d_product = Product::where('id', $product['product_id'])->first();
                if (!empty($variant->downloadable_product)) {
                    $output .= '<img src="' . get_file($variant->downloadable_product) . '" >';
                }
                if (!empty($d_product->downloadable_product)) {
                    $output .= '<img src="' . get_file($d_product->downloadable_product) . '" >';
                }
            }
            return $output;
        })
        ->filterColumn('order_id', function ($query, $keyword) {
            $query->where('product_order_id', 'like', "%$keyword%");
        })
        ->filterColumn('customer', function ($query, $keyword) {
            if (stripos('Guest', $keyword) !== false) {
                // Filter for guest customers
                $query->where('is_guest', 1);
            } elseif (stripos('Walk-in-customer', $keyword) !== false) {
                // Filter for walk-in customers
                $query->where('customer_id', 0);
            } else {
                // Filter for registered customers (searching by name)
                $query->whereHas('CustomerData', function ($subQuery) use ($keyword) {
                    $subQuery->where('first_name', 'like', "%$keyword%")
                             ->orWhere('last_name', 'like', "%$keyword%");
                });
            }
        })
        ->filterColumn('product_name', function ($query, $keyword) {
            $query->whereRaw('
                EXISTS (
                    SELECT 1
                    FROM JSON_TABLE(product_json, "$[*]" COLUMNS (name VARCHAR(255) PATH "$.product_name")) AS jt
                    WHERE jt.name LIKE ?
                )
            ', ["%$keyword%"]);
        })
        ->rawColumns(['order_id','product_name','customer','attachment','created_at']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Order $model): QueryBuilder
    {
        return $model->newQuery()
        ->select('orders.*', 'products.name as product_name')
        ->leftJoin('products', 'orders.product_id', '=', 'products.id')
        ->where('orders.theme_id', APP_THEME())
        ->where('orders.store_id', getCurrentStore());

    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('sales-downloadable-product-table')
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
        $bulkdeleteButtonConfig = [];
        if (module_is_active('BulkDelete')) {
            $bulkdeleteButtonConfig = bulkDeleteForm('sales-downloadable-product','sales-downloadable-product-table');
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
            Column::make('order_id')->title(__('Order Id')),
            Column::make('product_name')->title(__('Product Name')) ->name('product_name')->addClass('text-capitalize'),
            Column::make('customer')->title(__('Customer'))->addClass('text-capitalize'),
            Column::make('attachment')->title(__('Attachment')),
            Column::make('created_at')->title(__('Timestamp')),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'SalesDownloadableProduct_' . date('YmdHis');
    }
}
