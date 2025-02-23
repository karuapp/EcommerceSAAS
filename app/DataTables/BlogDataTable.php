<?php

namespace App\DataTables;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class BlogDataTable extends DataTable
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
                ->editColumn('cover_image_path', function (Blog $blog) {
                    // Ensure proper escaping of dynamic variables
                    $imageUrl = get_file($blog->cover_image_path, APP_THEME());
                    return '<img src="'. $imageUrl .'" alt="" width="100" class="cover_img_'. $blog->id .'">';
                })
                ->editColumn('category_id', function (Blog $blog) {
                    return $blog->category->name ?? '-';
                })
                ->addColumn('action', function (Blog $blog) {
                    return view('blog.action', compact('blog'));
                })
                ->filterColumn('category_id', function ($query, $keyword) {
                    // Assuming `permissions` is a relationship, adjust as needed
                    $query->whereHas('category', function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', "%$keyword%");
                    });
                })
                ->filterColumn('description', function ($query, $keyword) {
                    $query->whereRaw('LOWER(blogs.description) LIKE ?', ["%{$keyword}%"]);
                })
                ->filterColumn('deion', function ($query, $keyword) {
                    $query->whereRaw('LOWER(blogs.description) LIKE ?', ["%{$keyword}%"]);
                })
                ->filterColumn('short_deion', function ($query, $keyword) {
                    $query->whereRaw('LOWER(blogs.short_description) LIKE ?', ["%{$keyword}%"]);
                })
                ->orderColumn('description', function ($query, $direction) {
                    $query->orderby('blogs.description', $direction);
                })
                ->orderColumn('deion', function ($query, $direction) {
                    $query->orderby('blogs.description', $direction);
                })
                ->orderColumn('short_deion', function ($query, $direction) {
                    $query->orderby('blogs.short_description', $direction);
                })
                ->rawColumns(['cover_image_path','category_id','title','short_description','action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Blog $model): QueryBuilder
    {
        return $model->newQuery()->with('category')->where('theme_id', APP_THEME())->where('store_id',getCurrentStore());
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('blogs-table')
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

        // Bulk Delete Button
        $bulkdeleteButtonConfig = [];
        if (module_is_active('BulkDelete')) {
            $bulkdeleteButtonConfig = bulkDeleteForm('blog','blogs-table');
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
            Column::make('cover_image_path')->title(__('Cover Image')),
            Column::make('category_id')->title(__('Category')),
            Column::make('title')->title(__('Title')),
            Column::make('short_description')->title(__('Short Description'))->addClass('description-wrp'),
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
        return 'Blog_' . date('YmdHis');
    }
}
