<?php

namespace App\DataTables;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class TestimonialDataTable extends DataTable
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
            ->addColumn('action', function (Testimonial $testimonial) {
                return view('testimonial.action', compact('testimonial'));
            })
            ->editColumn('maincategory_id', function (Testimonial $testimonial) {
                $maincategory = !empty($testimonial->MainCategoryData) ? $testimonial->MainCategoryData->name : '';
                return $maincategory;
            })
            ->filterColumn('maincategory_id', function ($query, $keyword) {
                $query->whereHas('MainCategoryData', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('subcategory_id', function (Testimonial $testimonial) {
                $subcategory = !empty($testimonial->SubCategoryData) ? $testimonial->SubCategoryData->name : '';
                return $subcategory;
            })
            ->filterColumn('subcategory_id', function ($query, $keyword) {
                $query->whereHas('SubCategoryData', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('product_id', function (Testimonial $testimonial) {
                $product = !empty($testimonial->ProductData) ? $testimonial->ProductData->name : '';
                return $product;
            })
            ->filterColumn('product_id', function ($query, $keyword) {
                $query->whereHas('ProductData', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('rating_no', function ($testimonial) {
                $ratingHtml = '';

                for ($i = 0; $i < 5; $i++) {
                    $starClass = $i < $testimonial->rating_no ? 'text-warning' : '';
                    $ratingHtml .= '<i class="ti ti-star ' . $starClass . '"></i>';
                }

                return $ratingHtml;
            })
            ->filterColumn('description', function ($query, $keyword) {
                $query->whereRaw('LOWER(description) LIKE ?', ["%{$keyword}%"]);
            })
            ->filterColumn('deion', function ($query, $keyword) {
                $query->whereRaw('LOWER(description) LIKE ?', ["%{$keyword}%"]);
            })
            ->orderColumn('description', function ($query, $direction) {
                $query->orderby('description', $direction);
            })
            ->orderColumn('deion', function ($query, $direction) {
                $query->orderby('description', $direction);
            })
            ->rawColumns(['action', 'maincategory_id', 'subcategory_id', 'product_id', 'rating_no']);
        return $dataTable;
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Testimonial $model): QueryBuilder
    {
        return $model->where('theme_id', APP_THEME())->where('store_id', getCurrentStore());
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('testimonial-table')
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
            $bulkdeleteButtonConfig = bulkDeleteForm('testimonial','testimonial-table');
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
            Column::make('id')->searchable(false)->visible(false)->printable(false),
            Column::make('maincategory_id')->title(__('MainCategory')),
            Column::make('subcategory_id')->title(__('SubCategory')),
            Column::make('product_id')->title(__('Product')),
            Column::make('rating_no')->title(__('Rating')),
            Column::make('description')->title(__('Description'))->orderable(false)->addClass('description-wrp'),
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
        return 'Testimonial_' . date('YmdHis');
    }
}
