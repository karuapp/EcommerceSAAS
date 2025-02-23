<?php

namespace App\DataTables;

use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class SubCategoryDataTable extends DataTable
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
            ->addColumn('action', function (SubCategory $subcategory) {
                return view('subcategory.action', compact('subcategory'));
            })
            // ->editColumn('status', function (SubCategory $subcategory) {
            //     $status = $subcategory->status == 1 ? __('Active') : __('In-Active');
            //     return $status;
            // })
            ->editColumn('status', function ($subcategory) {
                if (!empty($subcategory->status)) {
                    return '<span class="badges fix_badges bg-success p-2 px-3 badge">' . __('Active') . '</span>';
                } else {
                    return '<span class="badges fix_badges bg-danger p-2 px-3 badge">' . __('In-Active') . '</span>';
                }
            })
            ->editColumn('maincategory_id', function (SubCategory $subcategory) {
                $category = $subcategory->MainCategory->name ?? '';
                return $category;
            })
            ->editColumn('image_path', function (SubCategory $subcategory) {
                if (!empty($subcategory->image_path)) {
                    $imagePath = get_file($subcategory->image_path, APP_THEME());
                    $html = '<img src="' . $imagePath . '" alt="" class="category_Image">';
                } else {
                    $html = '-';
                }
                return $html;
            })
            ->editColumn('icon_path', function (SubCategory $subcategory) {
                if (!empty($subcategory->icon_path)) {
                    $iconPath = get_file($subcategory->icon_path, APP_THEME());
                    $iconPath = '<img src="' . $iconPath . '" alt="" class="category_Image">';
                } else {
                    $iconPath = '-';
                }
                return $iconPath;
            })
            ->rawColumns(['action', 'image_path', 'maincategory_id', 'status', 'icon_path']);
        return $dataTable;
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(SubCategory $model): QueryBuilder
    {
        return $model->where('theme_id', APP_THEME())->where('store_id', getCurrentStore());
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {

        $dataTable = $this->builder()
            ->setTableId('subcategory-table')
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
            $bulkdeleteButtonConfig = bulkDeleteForm('subcategory','subcategory-table');
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
            Column::make('name')->title(__('Name')),
            Column::make('image_path')->title(__('Image')),
            Column::make('icon_path')->title(__('Icon')),
            Column::make('maincategory_id')->title(__('Category')),
            Column::make('status')->title(__('Status'))->addClass('text-capitalize'),
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
        return 'SubCategory_' . date('YmdHis');
    }
}
