<?php

namespace App\DataTables;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class OrderDataTable extends DataTable
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
            ->addColumn('action', function (Order $order) {
                return view('order.action', compact('order'));
            })
            ->editColumn('product_order_id', function ($item) {
                return '<div class="d-flex align-items-center">
                            <a href="' . route('order.view', \Illuminate\Support\Facades\Crypt::encrypt($item->id)) . '" class="btn btn-primary btn-sm text-sm" data-bs-toggle="tooltip" title="' . __('Invoice ID') . '">
                                <span class="btn-inner--icon"></span>
                                <span class="btn-inner--text">#' . $item->product_order_id . '</span>
                            </a>
                        </div>';
            })
            ->editColumn('order_date', function ($item) {
                return \App\Models\Utility::dateFormat($item->order_date);
            })
            ->editColumn('customer_id', function ($item) {
                if ($item->is_guest == 1) {
                    return __('Guest');
                } elseif ($item->customer_id != 0) {
                    return (!empty($item->CustomerData->name) ? $item->CustomerData->name : '') . '<br>' .
                        (!empty($item->CustomerData->mobile) ? $item->CustomerData->mobile : '');
                } else {
                    return __('Walk-in-customer');
                }
            })
            ->editColumn('final_price', function ($item) {
                return currency_format_with_sym(($item->final_price ?? 0), getCurrentStore(), APP_THEME()) ?? SetNumberFormat($item->final_price);
            })
            ->editColumn('payment_type', function ($item) {
                $paymentTypes = [
                    'cod' => __('Cash On Delivery'),
                    'bank_transfer' => __('Bank Transfer'),
                    'stripe' => __('Stripe'),
                    'paystack' => __('Paystack'),
                    'mercado' => __('Mercado Pago'),
                    'skrill' => __('Skrill'),
                    'paymentwall' => __('PaymentWall'),
                    'Razorpay' => __('Razorpay'),
                    'paypal' => __('Paypal'),
                    'flutterwave' => __('Flutterwave'),
                    'mollie' => __('Mollie'),
                    'coingate' => __('Coingate'),
                    'paytm' => __('Paytm'),
                    'POS' => __('POS'),
                    'toyyibpay' => __('Toyyibpay'),
                    'sspay' => __('Sspay'),
                    'Paytabs' => __('Paytabs'),
                    'iyzipay' => __('IyziPay'),
                    'payfast' => __('PayFast'),
                    'benefit' => __('Benefit'),
                    'cashfree' => __('Cashfree'),
                    'aamarpay' => __('Aamarpay'),
                    'telegram' => __('Telegram'),
                    'whatsapp' => __('Whatsapp'),
                    'paytr' => __('PayTR'),
                    'yookassa' => __('Yookassa'),
                    'midtrans' => __('Midtrans'),
                    'Xendit' => __('Xendit'),
                    'Nepalste' => __('Nepalste'),
                    'khalti' => __('Khalti'),
                    'AuthorizeNet' => __('AuthorizeNet'),
                    'Tap' => __('Tap'),
                    'PhonePe' => __('PhonePe'),
                    'Paddle' => __('Paddle'),
                    'Paiementpro' => __('Paiement Pro'),
                    'FedPay' => __('FedPay'),
                    'CinetPay' => __('CinetPay'),
                    'SenagePay' => __('SenagePay'),
                    'CyberSource' => __('CyberSource'),
                    'Ozow' => __('Ozow'),
                    'MyFatoorah' => __('MyFatoorah'),
                    'easebuzz' => __('Easebuzz'),
                    'NMI' => __('NMI'),
                    'PayU' => __('PayU'),
                    'sofort' => __('Sofort'),
                    'esewa' => __('Esewa'),
                    'Paynow' => __('Paynow'),
                    'DPO' => __('DPO'),
                    'Braintree' => __('Braintree'),
                    'PowerTranz' => __('PowerTranz'),
                    'SSLCommerz' => __('SSLCommerz'),
                    'Lottery Product' => __('Lottery Product')
                ];
                return $paymentTypes[$item->payment_type] ?? '-';
            })
            ->editColumn('delivered_status', function ($item) {
                $statusButtons = [
                    0 => '<button type="button" class="btn btn-sm btn-soft-info btn-icon bg-info badge-same">
                            <span class="btn-inner--icon"><i class="fas fa-check soft-info"></i></span>
                            <span class="btn-inner--text"> ' . __('Pending') . ' : ' . \App\Models\Utility::dateFormat($item->order_date) . ' </span>
                        </button>',
                    1 => '<button type="button" class="btn btn-sm btn-soft-success btn-icon bg-success badge-same">
                            <span class="btn-inner--text"> ' . __('Delivered') . ' : ' . \App\Models\Utility::dateFormat($item->delivery_date) . ' </span>
                        </button>',
                    2 => '<button type="button" class="btn btn-sm btn-soft-danger btn-icon bg-danger badge-same">
                            <span class="btn-inner--text"> ' . __('Cancel') . ' : ' . \App\Models\Utility::dateFormat($item->cancel_date) . ' </span>
                        </button>',
                    3 => '<button type="button" class="btn btn-sm btn-soft-danger btn-icon bg-danger badge-same">
                            <span class="btn-inner--text"> ' . __('Return') . ' : ' . \App\Models\Utility::dateFormat($item->return_date) . ' </span>
                        </button>',
                    4 => '<button type="button" class="btn btn-sm btn-soft-warning btn-icon bg-warning badge-same">
                            <span class="btn-inner--text"> ' . __('Confirmed') . ' : ' . \App\Models\Utility::dateFormat($item->confirmed_date) . ' </span>
                        </button>',
                    5 => '<button type="button" class="btn btn-sm btn-soft-secondary btn-icon bg-secondary badge-same">
                            <span class="btn-inner--icon"><i class="fas fa-check soft-secondary"></i></span>
                            <span class="btn-inner--text"> ' . __('Picked Up') . ' : ' . \App\Models\Utility::dateFormat($item->picked_date) . ' </span>
                        </button>',
                    6 => '<button type="button" class="btn btn-sm btn-soft-dark btn-icon bg-dark badge-same">
                            <span class="btn-inner--text"> ' . __('Shipped') . ' : ' . \App\Models\Utility::dateFormat($item->shipped_date) . ' </span>
                        </button>',
                    7 => '<button type="button" class="btn btn-sm btn-soft-dark btn-icon bg-dark badge-same">
                            <span class="btn-inner--text"> ' . __('Partially Paid') . ' : ' . \App\Models\Utility::dateFormat($item->order_date) . ' </span>
                        </button>',
                    8 => '<button type="button" class="btn btn-sm btn-soft-dark btn-icon bg-dark badge-same">
                            <span class="btn-inner--text"> ' . __('Pre Order') . ' : ' . \App\Models\Utility::dateFormat($item->order_date) . ' </span>
                        </button>',
                ];

                return $statusButtons[$item->delivered_status] ?? '';
            })
            ->rawColumns(['action', 'product_order_id', 'order_date', 'customer_id', 'final_price', 'payment_type', 'delivered_status']);
        return $dataTable;
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Order $model): QueryBuilder
    {
        return $model->where('theme_id', APP_THEME())->where('store_id', getCurrentStore())->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('order-table')
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
            $bulkdeleteButtonConfig = bulkDeleteForm('order', 'order-table');
        }

        $buttonsConfig = array_merge([
            $bulkdeleteButtonConfig,
            $exportButtonConfig,
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
            Column::make('product_order_id')->title(__('Order Id')),
            Column::make('order_date')->title(__('Date')),
            Column::make('customer_id')->title(__('Customer Info')),
            Column::make('final_price')->title(__('Price')),
            Column::make('payment_type')->title(__('Payment Type')),
            Column::make('delivered_status')->title(__('Order Status'))->addClass('text-capitalize'),
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
        return 'Order_' . date('YmdHis');
    }
}
