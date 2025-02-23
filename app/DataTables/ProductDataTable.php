<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ProductDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $admin = getAdminAllSetting();
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('action', function (Product $product) {
                return view('product.action', compact('product'));
            })
            ->editColumn('maincategory_id', function (Product $product) {
                return !empty($product->ProductData) ? $product->ProductData->name : '-';
            })
            ->filterColumn('maincategory_id', function ($query, $keyword) {
                $query->whereHas('ProductData', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('subcategory_id', function (Product $product) {
                return !empty($product->SubCategoryctData) ? $product->SubCategoryctData->name : '-';
            })
            ->filterColumn('subcategory_id', function ($query, $keyword) {
                $query->whereHas('SubCategoryctData', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('brand_id', function (Product $product) {
                return !empty($product->brand) ? $product->brand->name : '-';
            })
            ->filterColumn('brand_id', function ($query, $keyword) {
                $query->whereHas('brand', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('label_id', function (Product $product) {
                return !empty($product->label) ? $product->label->name : '-';
            })
            ->filterColumn('label_id', function ($query, $keyword) {
                $query->whereHas('label', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('variant_product', function (Product $product) {
                return $product->variant_product == 1 ? 'has variant' : 'no variant';
            })
            ->editColumn('average_rating', function (Product $product) {
                return '<i class="ti ti-star text-warning"></i> ' . $product->average_rating;
            })
            ->editColumn('price', function (Product $product) {
                if ($product->variant_product == 0) {
                    return currency_format_with_sym($product->price, getCurrentStore(), APP_THEME()) ?? SetNumberFormat($product->price);
                } else {
                    return __('In Variant');
                }
            })
            ->addColumn('stock_status', function ($product) use ($admin) {
                if ($product->variant_product == 1) {
                    return '<span class="badge badge-80 rounded p-2 f-w-600 bg-light-warning">' . __('In Variant') . '</span>';
                } else {
                    if ($product->track_stock == 0) {
                        if ($product->stock_status == 'out_of_stock') {
                            return '<span class="badge badge-80 rounded p-2 f-w-600 bg-light-danger">' . __('Out of stock') . '</span>';
                        } elseif ($product->stock_status == 'on_backorder') {
                            return '<span class="badge badge-80 rounded p-2 f-w-600 bg-light-warning">' . __('On Backorder') . '</span>';
                        } else {
                            return '<span class="badge badge-80 rounded p-2 f-w-600 bg-light-primary">' . __('In stock') . '</span>';
                        }
                    } else {
                        if ($product->product_stock <= (isset($admin['out_of_stock_threshold']) ? $admin['out_of_stock_threshold'] : 0)) {
                            return '<span class="badge badge-80 rounded p-2 f-w-600 bg-light-danger">' . __('Out of stock') . '</span>';
                        } else {
                            return '<span class="badge badge-80 rounded p-2 f-w-600 bg-light-primary">' . __('In stock') . '</span>';
                        }
                    }
                }
            })
            ->addColumn('product_stock', function ($product) {
                if ($product->variant_product == 1) {
                    return '-';
                } else {
                    return $product->product_stock > 0 ? $product->product_stock : '-';
                }
            })
            ->editColumn('cover_image_path', function (Product $product) {
                if (isset($product->cover_image_path) && !empty($product->cover_image_path)) {
                    return '<img src="' . get_file($product->cover_image_path, APP_THEME()) . '" alt="" width="100" class="cover_img' . $product->id . '">';
                }
                return '';
            })
            ->rawColumns(['action', 'maincategory_id', 'subcategory_id', 'brand_id', 'label_id', 'variant_product', 'average_rating', 'price', 'stock_status', 'product_stock', 'cover_image_path']);
        return $dataTable;
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Product $model): QueryBuilder
    {
        return $model->where('theme_id', APP_THEME())->where('product_type', null)->where('store_id', getCurrentStore());
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('maincategory-table')
            ->columns(array_merge(array_slice($this->getColumns(), 0, 1), bulkDeleteCloneCheckboxColumn(), array_slice($this->getColumns(), 1)))
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
                "info" => __("Showing") . " _START_ " . __("to") . " _END_ " . __("of") . " _TOTAL_ " . __("entries")
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
            $bulkdeleteButtonConfig = bulkDeleteForm('Product', 'maincategory-table');
        }

        $buttonsConfig = array_merge([
            $exportButtonConfig,
            $bulkdeleteButtonConfig,
            [
                'text' => '<i class="ti ti-arrow-back-up" data-bs-toggle="tooltip" title="' . __("Reset") . '" data-bs-original-title="' . __("Reset") . '"></i>',
                'extend' => 'reset',
                'className' => 'btn btn-light-info',
            ],
            [
                'text' => '<i class="ti ti-refresh" data-bs-toggle="tooltip" title="' . __("Reload") . '" data-bs-original-title="' . __("Reload") . '"></i>',
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
            Column::make('name')->title(__('Name')),
            Column::make('maincategory_id')->title(__('Category')),
            Column::make('subcategory_id')->title(__('Sub Category')),
            Column::make('brand_id')->title(__('Brand')),
            Column::make('label_id')->title(__('Label')),
            Column::make('cover_image_path')->title(__('Cover Image')),
            Column::make('variant_product')->title(__('Variant')),
            Column::make('average_rating')->title(__('Review')),
            Column::make('price')->title(__('Price')),
            Column::make('stock_status')->title(__('Stock Status'))->addClass('text-capitalize'),
            Column::make('product_stock')->title(__('Stock Quantity')),
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
        return 'Product_' . date('YmdHis');
    }
}
