<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PlanRequestController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\PlanCouponController;
use App\Http\Controllers\PaystackPaymentController;
use App\Http\Controllers\RazorpayPaymentController;
use App\Http\Controllers\MercadoPaymentController;
use App\Http\Controllers\SkrillPaymentController;
use App\Http\Controllers\PaymentWallPaymentController;
use App\Http\Controllers\PaypalPaymentController;
use App\Http\Controllers\FlutterwaveController;
use App\Http\Controllers\PaytmPaymentController;
use App\Http\Controllers\MolliePaymentController;
use App\Http\Controllers\CoingateController;
use App\Http\Controllers\SspayController;
use App\Http\Controllers\ToyyibpayController;
use App\Http\Controllers\PaytabsController;
use App\Http\Controllers\IyziPayController;
use App\Http\Controllers\BankTransferController;
use App\Http\Controllers\PayFastController;
use App\Http\Controllers\BenefitPaymentController;
use App\Http\Controllers\CashfreeController;
use App\Http\Controllers\AamarpayController;
use App\Http\Controllers\PaytrController;
use App\Http\Controllers\YookassaController;
use App\Http\Controllers\XenditPaymentController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\ShippingZoneController;
use App\Http\Controllers\ShippingMethodController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\CityController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\PixelFieldsController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\TaxOptionController;
use App\Http\Controllers\TaxMethodController;
use App\Http\Controllers\FlashSaleController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\MainCategoryController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DeliveryBoyController;
use App\Http\Controllers\ProductAttributeController;
use App\Http\Controllers\ProductAttributeOptionController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\ThemeSettingController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProductQuestionController;
use App\Http\Controllers\AccountProfileController;
use App\Http\Controllers\AddonController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AITemplateController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Customer\Auth\CustomerLoginController;
use App\Http\Controllers\ThemeAnalyticController;
use App\Http\Controllers\OrderNoteController;
use App\Http\Controllers\WoocomCategoryController;
use App\Http\Controllers\WoocomSubCategoryController;
use App\Http\Controllers\WoocomProductController;
use App\Http\Controllers\WoocomCustomerController;
use App\Http\Controllers\WoocomCouponController;
use App\Http\Controllers\ShopifyProductController;
use App\Http\Controllers\ShopifyCategoryController;
use App\Http\Controllers\ShopifyCustomerController;
use App\Http\Controllers\ShopifyCouponController;
use App\Http\Controllers\ShopifySubCategoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\OrderRefundController;
use App\Http\Controllers\ContactController;
use App\Http\Middleware\ActiveTheme;
use App\Http\Controllers\TagController;
use App\Http\Controllers\NepalstePaymnetController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\KhaltiPaymnetController;
use App\Http\Controllers\PayHerePaymnetController;
use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\AuthorizeNetPaymnetController;
use App\Http\Controllers\TapPaymnetController;
use App\Http\Controllers\PhonePePaymentController;
use App\Http\Controllers\PaddlePaymentController;
use App\Http\Controllers\PaiementProPaymentController;
use App\Http\Controllers\FedPayPaymentController;
use App\Http\Controllers\ProductLabelController;
use App\Http\Controllers\CinetPayController;
use App\Http\Controllers\SenangPayController;
use App\Http\Controllers\CyberSourceController;
use App\Http\Controllers\OzowController;
use App\Http\Controllers\EasebuzzController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\MyFatoorahController;
use App\Http\Controllers\NMIPayController;
use App\Http\Controllers\PayUPaymentController;
use App\Http\Controllers\OfertemagController;
use App\Http\Controllers\PaynowController;
use App\Http\Controllers\SofortController;
use App\Http\Controllers\ESewaPaymentController;
use App\Http\Controllers\DPOPayController;
use App\Http\Controllers\BraintreeController;
use App\Http\Controllers\PowertranzPaymentController;
use App\Http\Controllers\SSLCommerzPaymentController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['web'])->group(function () {
// Route::get('/', function () {
//     return view('welcome');
// });
require __DIR__ . '/auth.php';
//Route::get('/', [HomeController::class, 'Landing'])->name('landing')->middleware('setlocate');

Route::get('change-languages/{lang}', [LanguageController::class, 'changelanguage'])->name('changelanguage')->middleware('setlocate');

Route::get('add-on/details/{slug}', [HomeController::class, 'SoftwareDetails'])->name('software.details')->middleware('setlocate');

// module page before login
Route::get('add-on', [HomeController::class, 'Software'])->name('apps.software');
Route::any('/', [HomeController::class, 'Landing'])->name('start')->middleware('setlocate');
Route::get('pricing', [HomeController::class, 'Pricing'])->name('apps.pricing');

// Session Save Addon Routes
Route::get('add-on/session/create', [ModuleController::class, 'AddOnSessioncreate'])->name('add-on.session.create');
Route::post('add-on/session/save', [ModuleController::class, 'AddOnSessionSave'])->name('add-on.session.save');

  Route::middleware(['auth', 'xss', 'setlocate', 'verified'])->group(function () {

    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::post('/top-brand-category-chart', [HomeController::class, 'top_brand_category_chart'])->name('top.brand.category.chart');

    Route::get('themean-alytic', [ThemeAnalyticController::class, 'index'])->name('theme_analytic');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('auth.profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::get('user-info/{id}', [UserController::class, 'userInfo'])->name('user.info');
    Route::post('user-unable', [UserController::class, 'userUnable'])->name('user.unable');
    Route::get('user-enable-login/{id}', [UserController::class, 'userLoginManage'])->name('users.enable.login');

    //*************************country-state-city**********************
    Route::resource('countries', CountryController::class);
    Route::resource('state', StateController::class);
    Route::resource('city', CityController::class);
    Route::get('/stock-report/low-stock', [ReportController::class, 'getLowStockProducts'])->name('stockReport.lowStock');
    Route::get('/stock-report/out-stock', [ReportController::class, 'getOutOfStockProducts'])->name('stockReport.outOfStock');
    Route::get('/stock-report/most-stocked', [ReportController::class, 'getMostStockedProducts'])->name('stockReport.mostStocked');

    Route::post('/get-country', [CountryController::class, 'getCountry'])->name('get.country');
    Route::post('/country-tab', [CountryController::class, 'getAllCountry'])->name('country.tab');
    Route::post('/get-state', [StateController::class, 'getState'])->name('get.state');
    Route::post('/get-city-state', [StateController::class, 'getCityState'])->name('getcitystate');
    Route::get('/get-all-city', [StateController::class, 'getAllState'])->name('get.all.state');

    Route::resource('plan', PlanController::class);

    //strip
    Route::get('/stripe/{code}', [StripePaymentController::class, 'stripe'])->name('stripe.plan');

    Route::get('/refund/{id}/{user_id}', [PlanController::class, 'refund'])->name('plan.order.refund');

    Route::resource('plan-request', PlanRequestController::class);
    Route::get('request-send/{id}', [PlanRequestController::class, 'userRequest'])->name('send.request');
    Route::get('request-cancel/{id}', [PlanRequestController::class, 'cancelRequest'])->name('request.cancel');
    Route::get('request-response/{id}/{response}', [PlanRequestController::class, 'acceptRequest'])->name('response.request');

    // *************************PlanCoupon*****************************
    Route::resource('plan-coupon', PlanCouponController::class);
    Route::get('/apply-coupon', [PlanCouponController::class, 'applyCoupon'])->name('apply.coupon');
    Route::resource('setting', SettingController::class);
    Route::post('storage-settings', [SettingController::class, 'StorageSettings'])->name('storage.settings');
    Route::post('business-settings', [SettingController::class, 'BusinessSettings'])->name('business.settings');
    Route::post('/payment-setting', [SettingController::class, 'PaymentSetting'])->name('payment.settings');
    Route::post('/setting-form', [SettingController::class, 'settingForm'])->name('setting.form');

    Route::get('plan/prepare-amount', [PlanController::class, 'planPrepareAmount'])->name('plan.prepare.amount');

    // cinetpay
    Route::post('/plan-pay-with-cinetpay', [CinetPayController::class, 'planPayWithCinetpay'])->name('plan.pay.with.cinetpay');
    Route::match(['get', 'post'], '/plan-cinetpay-payment-return', [CinetPayController::class, 'return_url'])->name('plan.cinetpay.return');
    Route::match(['get', 'post'], '/plan-cinetpay-payment-notify', [CinetPayController::class, 'notify_url'])->name('plan.get.cinetpay.notify');


    // strip
    Route::get('/plan-stripe/{code}', [StripePaymentController::class, 'stripe'])->name('stripe');
    Route::post('plan-stripe-payment', [StripePaymentController::class, 'addpayment'])->name('stripe.payment');

    // Paystack
    Route::post('/plan-pay-with-paystack', [PaystackPaymentController::class, 'planPayWithPaystack'])->name('plan.pay.with.paystack');
    Route::get('/plan-paystack-payment/{pay_id}/{plan_id}', [PaystackPaymentController::class, 'getPaymentStatus'])->name('plan.paystack');


    Route::post('/plan-pay-with-razorpay', [RazorpayPaymentController::class, 'planPayWithRazorpay'])->name('plan.pay.with.razorpay');
    Route::get('/plan-razorpay-payment/{txref}/{plan_id}', [RazorpayPaymentController::class, 'getPaymentStatus'])->name('plan.razorpay');

    Route::post('/plan-pay-with-mercado', [MercadoPaymentController::class, 'planPayWithMercado'])->name('plan.pay.with.mercado');
    Route::get('/plan-mercado-payment/{plan}', [MercadoPaymentController::class, 'getPaymentStatus'])->name('plan.mercado');


    Route::post('/plan-pay-with-skrill', [SkrillPaymentController::class, 'planPayWithSkrill'])->name('plan.pay.with.skrill');
    Route::get('/plan-skrill-payment/{plan}', [SkrillPaymentController::class, 'getPaymentStatus'])->name('plan.skrill');


    Route::post('/plan-pay-with-paymentwalls', [PaymentWallPaymentController::class, 'paymentwall'])->name('plan.paymentwallpayment');
    Route::post('/plan-paymentwalls-payment/{plan}', [PaymentWallPaymentController::class, 'planPayWithPaymentWall'])->name('plan.pay.with.paymentwall');
    Route::get('/plan/{flag}', [PaymentWallPaymentController::class, 'planeerror'])->name('error.plan.show');


    Route::post('plan-pay-with-paypal', [PaypalPaymentController::class, 'addpayment'])->name('paypal.payment');
    Route::get('{id}/{amount}/plan-paypal-payment/', [PaypalPaymentController::class, 'planGetPaymentStatus'])->name('plan.get.payment.status');


    Route::post('/plan-pay-with-flutterwave', [FlutterwaveController::class, 'addpayment'])->name('flutterwave.payment');
    Route::get('/plan-flaterwave-payment/{txref}/{plan_id}', [FlutterwaveController::class, 'getPaymentStatus'])->name('plan.flaterwave');


    Route::post('/plan-pay-with-paytm', [PaytmPaymentController::class, 'planPayWithPaytm'])->name('plan.pay.with.paytm');
    Route::post('/plan-paytm-payment/{plan_id}', [PaytmPaymentController::class, 'getPaymentStatus'])->name('plan.paytm');


    Route::post('/plan-pay-with-mollie', [MolliePaymentController::class, 'planPayWithMollie'])->name('plan.pay.with.mollie');
    Route::get('/plan-mollie-payment/{plan}', [MolliePaymentController::class, 'getPaymentStatus'])->name('plan.mollie');

    Route::post('plan-pay-with-coingate', [CoingateController::class, 'coingatePaymentPrepare'])->name('coingate.prepare.plan');
    Route::get('plan-coingate-payment', [CoingateController::class, 'coingatePlanGetPayment'])->name('coingate.coingate.callback');

    Route::post('plan-pay-with-sspay', [SspayController::class, 'SspayPaymentPrepare'])->name('sspay.prepare.plan');
    Route::get('plan-sspay-payment/{plan_id}/{amount}/{couponCode}', [SspayController::class, 'SspayPlanGetPayment'])->name('plan.sspay.callback');

    Route::post('plan-pay-with-toyyibpay', [ToyyibpayController::class, 'toyyibpayPaymentPrepare'])->name('toyyibpay.prepare.plan');
    Route::get('plan-toyyibpay-payment/{plan_id}/{amount}/{couponCode}', [ToyyibpayController::class, 'toyyibpayPlanGetPayment'])->name('plan.toyyibpay.callback');

    Route::post('plan-pay-with-paytabs', [PaytabsController::class, 'PaytabsPaymentPrepare'])->name('paytabs.prepare.plan');
    Route::post('/plan-paytabs-payment', [PaytabsController::class, 'planGetPaymentStatus'])->name('plan.paytabs.callback');

    Route::post('plan-pay-with-iyzipay', [IyziPayController::class, 'initiatePayment'])->name('iyzipay.payment.init');
    Route::post('iyzipay/callback/plan/{id}/{amount}/{coupan_code?}', [IyzipayController::class, 'iyzipayCallback'])->name('iyzipay.payment.callback');

    Route::post('plan-pay-with-bank', [BankTransferController::class, 'planPayWithbank'])->name('plan.pay.with.bank');
    Route::get('plan/orders/show/{id}', [BankTransferController::class, 'show'])->name('plan.order.show');
    Route::delete('/bank_transfer/{order}/', [BankTransferController::class, 'destroy'])->name('bank_transfer.destroy');
    Route::any('order_approve/{id}', [BankTransferController::class, 'orderapprove'])->name('order.approve');
    Route::any('order_reject/{id}', [BankTransferController::class, 'orderreject'])->name('order.reject');

    Route::post('plan-pay-with-payfast', [PayFastController::class, 'index'])->name('payfast.payment');
    Route::any('plan-payfast-payment/{success}', [PayFastController::class, 'success'])->name('payfast.payment.success');

    Route::any('plan-pay-with-Benefit', [BenefitPaymentController::class, 'initiatePayment'])->name('benefit.initiate');
    Route::any('plan-Benefit-payment', [BenefitPaymentController::class, 'call_back'])->name('benefit.call_back');

    Route::post('plan-pay-with-cashfree', [CashfreeController::class, 'cashfreePayment'])->name('cashfree.payment');
    Route::any('cashfree/payments/success', [CashfreeController::class, 'cashfreePaymentSuccess'])->name('cashfreePayment.success');


    Route::post('aamarpay/payment', [AamarpayController::class, 'paywithaamarpay'])->name('pay.aamarpay.payment');
    Route::any('aamarpay/success/{data}', [AamarpayController::class, 'aamarpaysuccess'])->name('pay.aamarpay.success');

    Route::post('plan-pay-with-paytr', [PaytrController::class, 'PlanpayWithPaytr'])->name('plan.pay.with.paytr');
    Route::any('plan-paytr-payment/success/', [PaytrController::class, 'paytrsuccessCallback'])->name('pay.paytr.success');

    Route::post('plan-pay-with-yookassa', [YookassaController::class, 'paywithyookassa'])->name('pay.yookassa.payment');
    Route::get('/plan-yookassa-payment/success', [YookassaController::class, 'planGetYooKassaStatus'])->name('plan.get.yookassa.status');

    Route::get('plan-pay-with-Xendit', [XenditPaymentController::class, 'PaywithXendit'])->name('pay.Xendit.payment');
    Route::any('plan-xendit-payment/status', [XenditPaymentController::class, 'planGetXenditStatus'])->name('plan.xendit.status');


    Route::post('plan-pay-with-midtrans', [MidtransController::class, 'paywithMidtrans'])->name('pay.midtrans.payment');
    Route::any('/plan-midtrans-payment', [MidtransController::class, 'planGetMidtransStatus'])->name('plan.get.midtrans.status');
    Route::post('/recaptcha-settings', [SettingController::class, 'RecaptchaSetting'])->name('recaptcha.settings');
    Route::post('loyality-program-settings', [SettingController::class, 'LoyalityProgramSettings'])->name('loyality.program.settings');
    Route::resource('pixel-setting', PixelFieldsController::class);

    Route::post('plan-pay-with-nepalste', [NepalstePaymnetController::class, 'planPayWithnepalste'])->name('pay.nepalste.payment');
    Route::get('nepalste/status/', [NepalstePaymnetController::class, 'planGetNepalsteStatus'])->name('nepalste.status');
    Route::get('nepalste/cancel/', [NepalstePaymnetController::class, 'planGetNepalsteCancel'])->name('nepalste.cancel');

    Route::post('plan-disable', [PlanController::class, 'planDisable'])->name('plan.disable');
    Route::get('plan-trial/{id}', [PlanController::class, 'planTrial'])->name('plan.trial');

    Route::post('plan-pay-with-khalti', [KhaltiPaymnetController::class, 'planPayWithKhalti'])->name('pay.khalti.payment');
    Route::post('plan-get-khalti-status', [KhaltiPaymnetController::class, 'planGetKhaltiStatus'])->name('plan.get.khalti.status');

    Route::post('plan-pay-with-payhere', [PayHerePaymnetController::class, 'planPayWithPayHere'])->name('pay.payhere.payment');
    Route::any('plan-get-payhere-status/{plan_id}', [PayHerePaymnetController::class, 'planGetPayHereStatus'])->name('plan.get.payhere.status');

    Route::post('plan-pay-with-authorizenet', [AuthorizeNetPaymnetController::class, 'planPayWithAuthorizeNet'])->name('pay.authorizenet.payment');
    Route::post('/plan-get-authorizenet-status', [AuthorizeNetPaymnetController::class, 'planPayWithAuthorizeNetData'])->name('plan.get.authorizenet.status');

    Route::post('plan-pay-with-tap', [TapPaymnetController::class, 'planPayWithTap'])->name('pay.tap.payment');
    Route::get('/plan-get-tap-status', [TapPaymnetController::class, 'planGetTapStatus'])->name('plan.get.tap.status');

    Route::post('plan-pay-with-phonepe', [PhonePePaymentController::class, 'planPayWithPhonePe'])->name('pay.phonepe.payment');
    Route::any('/plan-get-phonepe-status', [PhonePePaymentController::class, 'planGetPhonePeStatus'])->name('plan.get.phonepe.status');

    Route::post('plan-pay-with-paddle', [PaddlePaymentController::class, 'planPayWithPaddle'])->name('pay.paddle.payment');
    Route::any('/plan-get-paddle-status', [PaddlePaymentController::class, 'planGetPaddleStatus'])->name('plan.get.paddle.status');
    Route::any('/plan-get-paddle', [PaddlePaymentController::class, 'planGetStatus'])->name('plan.get.paddle');

    Route::post('plan-pay-with-paiementpro', [PaiementProPaymentController::class, 'planPayWithPaiementpro'])->name('pay.paiementpro.payment');
    Route::any('/plan-get-paiementpro-status', [PaiementProPaymentController::class, 'planGetPaiementproStatus'])->name('plan.get.paiementpro.status');

    Route::post('plan-pay-with-fedpay', [FedPayPaymentController::class, 'planPayWithFedPay'])->name('pay.fedpay.payment');
    Route::any('/plan-get-fedpay-status', [FedPayPaymentController::class, 'planGetFedPayStatus'])->name('plan.get.fedpay.status');

    // easebuzz
    Route::post('/plan-pay-with-easebuzz', [EasebuzzController::class, 'planPayWithEasebuzz'])->name('plan.pay.with.easebuzz');
    Route::match(['get', 'post'], '/plan-easebuzz-payment-return', [EasebuzzController::class, 'return_url'])->name('plan.easebuzz.return');
    Route::match(['get', 'post'], 'plan-easebuzz-payment-notify', [EasebuzzController::class, 'notify_url'])->name('plan.get.easebuzz.notify');

    //nmi
    Route::post('/plan/company/nmi', [NMIPayController::class, 'planPayWithNMI'])->name('plan.pay.with.nmi');
    Route::any('/plan/company/nmi/payment/{plan_id}', [NMIPayController::class, 'planPayWithNMIView'])->name('plan.pay.with.nmi.view');

    //PayU
    Route::post('/plan-pay-with-payu', [PayUPaymentController::class, 'planPayWithPayU'])->name('plan.pay.with.payu');
    Route::any('/plan-payu-payment/status/{plan_id}', [PayUPaymentController::class, 'planGetPayUStatus'])->name('plan.payu.status');
    
    // Paynow
    Route::post('plan-pay-with/paynow', [PaynowController::class, 'planPayWithPaynow'])->name('plan.pay.with.Paynow');
    Route::get('plan-get-Paynow-status/{plan_id}', [PaynowController::class, 'planGetPaynowStatus'])->name('plan.get.Paynow.status');

    //MyFatoorah
    Route::post('/plan/company/myfatoorah', [MyFatoorahController::class, 'planPayWithmyfatoorah'])->name('plan.pay.with.myfatoorah');
    Route::any('/myfatoorah/call_back', [MyFatoorahController::class, 'paymentCallback'])->name('myfatoorah.call_back');
    // Sofort
    Route::post('plan-sofort-payment', [SofortController::class, 'addpayment'])->name('sofort.payment');
    Route::post('/plan-pay-with-soforts', [SofortController::class, 'failure'])->name('plan.payment.failure');
    Route::any('/plan-pay-with-sofort', [SofortController::class, 'success'])->name('plan.payment.success');

    // ESewa
    Route::post('plan-esewa-payment', [ESewaPaymentController::class, 'addpayment'])->name('esewa.payment');
    Route::post('/plan-pay-with-esewa', [ESewaPaymentController::class, 'failure'])->name('plan.esewa.payment.failure');
    Route::any('/plan-pay-with-esewa', [ESewaPaymentController::class, 'success'])->name('plan.esewa.payment.success');

    // DPO
    Route::any('plan-pay-with/DPO/create', [DPOPayController::class, 'create'])->name('plan.dpo.view');
    Route::any('plan-pay-with/DPO', [DPOPayController::class, 'planPayWithDPOPay'])->name('plan.pay.with.dpo');
    Route::any('plan-get-DPO/{plan_id}', [DPOPayController::class, 'planGetDPOPayStatus'])->name('plan.get.dpo.status');

    //Braintree

    Route::post('/plan/payment', [BraintreeController::class, 'planPayWithBraintree'])->name('plan.pay.with.braintree');
    Route::post('/plan/payment/status', [BraintreeController::class, 'planGetBraintreeStatus'])->name('plan.braintree.status');
    Route::get('pay', [BraintreeController::class, 'pay'])->name('braintree.pay');

    //Powertranz
    Route::any('plan-pay-with/Powertranz/create', [PowertranzPaymentController::class, 'create'])->name('plan.Powertranz.view');
    Route::any('plan-pay-with/Powertranz', [PowertranzPaymentController::class, 'planPayWithPowertranz'])->name('plan.pay.with.Powertranz');
    Route::any('plan-get-Powertranz-status/{plan_id}', [PowertranzPaymentController::class, 'planGetPowertranzStatus'])->name('plan.get.Powertranz.status');

    //SSLCommerz
    Route::post('plan-pay-with/sslcommerz', [SSLCommerzPaymentController::class, 'planPayWithSSLCommerz'])->name('plan.sslcommerz.view');
    Route::any('/plan/sslcommerz/status/{plan_id}', [SSLCommerzPaymentController::class, 'planGetSSLCommerzStatus'])->name('plan.get.sslcommerz.status');

    ///*************************cache setting******************** ***/
    Route::get('/config-cache', function () {
      session()->put(['setting_tab' => 'cache_setting']);
      Artisan::call('cache:clear');
      Artisan::call('route:clear');
      Artisan::call('view:clear');
      Artisan::call('optimize:clear');
      Artisan::call('config:clear');
      return redirect()->back()->with('success', 'Clear Cache successfully.');
    })->withoutMiddleware('setlocate');

    // **************************style custmization**************************
    Route::post('/customize-settings', [SettingController::class, 'CustomizeSetting'])->name('customize.settings');
    Route::post('/meta-seo-settings', [SettingController::class, 'SEOSetting'])->name('meta-seo.settings');

    Route::post('twilio-settings', [SettingController::class, 'TwilioSettings'])->name('twilio.settings');
    Route::post('chatgpt-setting', [SettingController::class, 'ChatgptSettings'])->name('chatgpt.setting');
    Route::post('stock-settings', [SettingController::class, 'StockSettings'])->name('stock.settings');

    /** Email Setting Routes */
    Route::post('email-settings', [SettingController::class, 'saveEmailSettings'])->name('email.settings');
    Route::post('email-test', [SettingController::class, 'TestMail'])->name('email.test');
    Route::post('test-send-mail', [SettingController::class, 'testSendMail'])->name('test.send.mail');
    Route::post('get-email-fields', [SettingController::class, 'getEmailSettingFields'])->name('get.email.fields');
    Route::post('email-notification-status', [EmailTemplateController::class, 'updateEmailNotificationStatus'])->name('update.email.statue');

    /** Email Templates Routes */
    Route::get('email_template_lang/{lang?}', [EmailTemplateController::class, 'emailTemplate'])->name('email_template');
    Route::get('email_template_lang/{id}/{lang?}', [EmailTemplateController::class, 'manageEmailLang'])->name('manage.email.language');
    Route::put('email_template_lang/{id}/', [EmailTemplateController::class, 'updateEmailSettings'])->name('updateEmail.settings');

    /** Shipping Routes */
    Route::resource('shipping', ShippingController::class);

    /** Shipping Zone Routes */
    Route::resource('shipping-zone', ShippingZoneController::class);
    Route::post('shipping-states-list', [ShippingZoneController::class, 'states_list'])->name('shipping-states.list');

    /** Shipping Method Routes */
    Route::resource('shipping-method', ShippingMethodController::class);
    Route::get('{id}/free-shipping-edit', [ShippingMethodController::class, 'freeShippingEdit'])->name('free-shipping.edit');
    Route::post('{id}/free-shipping', [ShippingMethodController::class, 'freeShippingUpdate'])->name('free-shipping.update');
    Route::get('{id}/local-shipping-edit', [ShippingMethodController::class, 'localShippingEdit'])->name('local-shipping.edit');
    Route::post('{id}/local-shipping', [ShippingMethodController::class, 'localShippingUpdate'])->name('local-shipping.update');

    /** Blog Routes */
    Route::resource('blog', BlogController::class);
    /** Blog  Category Routes */
    Route::resource('blog-category', BlogCategoryController::class);

    // webhook Setting
    Route::resource('webhook', WebhookController::class);

    /** Tax Routes */
    Route::resource('taxes', TaxController::class);
    Route::resource('taxes-method', TaxMethodController::class)->except(['create']);
    Route::get('taxes-method/create/{tax_option_id}', [TaxMethodController::class, 'create'])->name('taxes-method.create');
    Route::post('tax-option-settings', [TaxOptionController::class, 'taxSettings'])->name('tax-option.settings');
    Route::post('cities-list', [TaxMethodController::class, 'cities_list'])->name('cities.list');
    Route::post('states-list', [ShippingZoneController::class, 'states_list'])->name('states.lists');

    /** Flash Sale */
    Route::resource('flash-sale', FlashSaleController::class);
    Route::post('update-flashsale-status', [FlashsaleController::class, 'updateStatus'])->name('update-flashsale-status');
    Route::post('get-options', [FlashSaleController::class, 'get_options'])->name('get.options');
    /** Faqs Routes */
    Route::resource('faqs', FaqController::class);
    // Store Setting
    Route::resource('stores', StoreController::class);
    Route::get('stores/user/create', [StoreController::class, 'userCreate'])->name('store.user.create');
    Route::get('store/customdomain', [StoreController::class, 'customDomain'])->name('store.customdomain');
    Route::get('store/subdomain', [StoreController::class, 'subDomain'])->name('store.subdomain');
    Route::get('store/change-status', [StoreController::class, 'changeStatus'])->name('store.active.status');
    Route::get('store/list', [StoreController::class, 'list'])->name('store.list');

    Route::post('pwa-settings/{id}', [StoreController::class, 'pwaSetting'])->name('pwa.setting');
    Route::post('/cookie-setting', [SettingController::class, 'CookieSettings'])->name('cookie.setting');
    Route::any('/cookie-consent', [SettingController::class, 'CookieConsent'])->name('cookie-consent');

    Route::resource('stores', StoreController::class);
    Route::get('store-reset-password/{id}', [StoreController::class, 'storeResetPassword'])->name('stores.reset.password');
    Route::post('store-reset-password-update/{id}', [StoreController::class, 'storeResetPasswordUpdate'])->name('stores.reset.password.update');

    Route::get('/change-store/{id}', [StoreController::class, 'changeStore'])->name('change.store');

    Route::resource('testimonial', TestimonialController::class);
    Route::post('get-subcategory', [TestimonialController::class, 'get_subcategory'])->name('get.subcategory');
    Route::post('get-product', [TestimonialController::class, 'get_product'])->name('get.product');

    Route::resource('main-category', MainCategoryController::class);
    Route::resource('sub-category', SubCategoryController::class);
    Route::resource('product', ProductController::class);
    Route::get('product-list', [ProductController::class, 'list'])->name('product.list');

    Route::post('get-slug', [ProductController::class, 'get_slug'])->name('get.slug');
    Route::post('get-product-subcategory', [ProductController::class, 'get_subcategory'])->name('get.product.subcategory');



    // Menus Routes
    Route::resource('menus', MenuController::class)->except(['update']);
    Route::get('add-cat-to-menu', [MenuController::class, 'addCatToMenu'])->name('menus.addCategory');
    Route::get('add-page-to-menu', [MenuController::class, 'addPageToMenu'])->name('menus.addPage');
    Route::get('add-brand-to-menu', [MenuController::class, 'addBrandToMenu'])->name('menus.addBrand');
    Route::get('update-menu', [MenuController::class, 'updateMenu'])->name('menus.update');
    Route::post('update-menuitem/{id}', [MenuController::class, 'updateMenuItem'])->name('menus.updateItems');
    Route::get('add-custom-link', [MenuController::class, 'addLinkToMenu'])->name('menus.addLink');
    Route::get('delete-menuitem/{id}/{key}/{in?}', [MenuController::class, 'deleteMenuItem'])->name('menus.deleteItems');

    // Pages Routes
    Route::resource('pages', PageController::class);
    Route::post('update-page-status', [PageController::class, 'updateStatus'])->name('update-page-status');

    Route::resource('deliveryboy', DeliveryBoyController::class);
    Route::get('deliveryboy/reset-password/{id}', [DeliveryBoyController::class, 'resetPassword'])->name('deliveryboy.reset');
    Route::post('deliveryboy/reset/{id}', [DeliveryBoyController::class, 'updatePassword'])->name('deliveryboy.reset.password');
    Route::get('deliveryboy-list', [DeliveryBoyController::class, 'list'])->name('deliveryboy.list');

    Route::post('order-assign', [OrderController::class, 'order_assign'])->name('order.assign');

    Route::resource('product-attributes', ProductAttributeController::class);

    Route::post('products/attrinbute_option', [ProductController::class, 'attribute_option'])->name('products.attribute_option');

    Route::post('products/attribute_combination', [ProductController::class, 'attribute_combination'])->name('products.attribute_combination');

    Route::get('add-attribute-option/{id?}', [ProductAttributeOptionController::class, 'create'])->name('product-attribute-option.create');

    Route::get('attribute-option/{id?}', [ProductAttributeOptionController::class, 'show'])->name('product-attribute-option.show');

    Route::post('store-attribute-option/{id?}', [ProductAttributeOptionController::class, 'store'])->name('product-attribute-option.store');

    Route::post('product-attribute/option', [ProductAttributeOptionController::class, 'option'])->name('attribute-option');

    Route::get('edit-attribute-option/{id?}', [ProductAttributeOptionController::class, 'edit'])
      ->name('product-attribute-option.edit');

    Route::delete('delete-attribute-option/{id?}', [ProductAttributeOptionController::class, 'destroy'])->name('product-attribute-option.destroy');

    Route::post('update-attribute-option/{id?}', [ProductAttributeOptionController::class, 'update'])->name('product-attribute-option.update');

    Route::post('products/attribute_combination', [ProductController::class, 'attribute_combination'])->name('products.attribute_combination');

    Route::post('products/sku_combination', [ProductController::class, 'sku_combination'])->name('products.sku_combination');

    Route::delete('products/{id}/delete', [ProductController::class, 'file_delete'])->name('products.file.detele');

    Route::put('products/attribute_combination/edit', [ProductController::class, 'attribute_combination_edit'])->name('products.attribute_combination_edit');
    Route::put('products/sku_combination/edit', [ProductController::class, 'sku_combination_edit'])->name('products.sku_combination_edit');
    Route::put('products/attribute_combination_data/edit', [ProductController::class, 'attribute_combination_data'])->name('products.attribute_combination_data');

    Route::delete('product-attribute-delete/{id}', [ProductController::class, 'product_attribute_delete'])->name('product.attribute.delete');

    Route::resource('app-setting', AppSettingController::class);
    Route::get('mobile-screen-content', [AppSettingController::class, 'MobileScreenContent'])->name('mobilescreen.content');
    Route::post('theme-settings', [AppSettingController::class, 'ThemeSettings'])->name('theme.settings');
    Route::DELETE('ownerstore-remove/{id}', [StoreController::class, 'ownerstoredestroy'])->name('ownerstore.remove');

    Route::post('/seo-setting', [AppSettingController::class, 'seoSettings'])->name('seo.settings');
    Route::post('/shipping-label-setting', [AppSettingController::class, 'shippingLabelSettings'])->name('shippinglabel.settings');

    Route::post('site-setting', [AppSettingController::class, 'SiteSetting'])->name('site.setting');
    // Coupon Routes
    Route::get('coupon/export', [CouponController::class, 'fileExport'])->name('coupon.export');
    Route::resource('coupon', CouponController::class);

    //Woocommerce
    Route::post('woocommerce-settings', [SettingController::class, 'WoocommerceSettings'])->name('woocommerce.settings');

    Route::resource('woocom_coupon', WoocomCouponController::class);
    Route::resource('woocom_product', WoocomProductController::class);
    Route::resource('woocom_category', WoocomCategoryController::class);
    Route::resource('woocom_sub_category', WoocomSubCategoryController::class);
    Route::resource('woocom_customer', WoocomCustomerController::class);

    // shopify
    Route::post('shopify-settings', [SettingController::class, 'shopifySettings'])->name('shopify.settings');

    Route::resource('shopify_product', ShopifyProductController::class);
    Route::resource('shopify_category', ShopifyCategoryController::class);
    Route::resource('shopify_customer', ShopifyCustomerController::class);
    Route::resource('shopify_coupon', ShopifyCouponController::class);
    Route::resource('shopify_sub_category', ShopifySubCategoryController::class);

    Route::post('whatsapp-settings', [SettingController::class, 'WhatsappSettings'])->name('whatsapp.settings');
    // Route::post('custom-msg', [StoreController::class, 'customMassage'])->name('customMassage');
    Route::get('store/{id}/login-with-admin', [StoreController::class, 'LoginWithAdmin'])->name('login.with.admin');
    Route::get('login-with-admin/exit', [StoreController::class, 'ExitAdmin'])->name('exit.admin');
    Route::get('{id}/stores-link', [StoreController::class, 'StoreLinks'])->name('stores.link');
    Route::get('stores/{id}/plan', [StoreController::class, 'upgradePlan'])->name('plan.upgrade');
    Route::get('store-plan-active/{id}/plan/{pid}', [StoreController::class, 'activePlan'])->name('plan.active');

    /** System Setting Routes */
    Route::post('system-settings', [SettingController::class, 'SystemSettings'])->name('system.settings');

    /** Currency Setting Routes */
    Route::post('currency-settings', [SettingController::class, 'currencySettings'])->name('currency.settings');
    Route::post('update-note-value', [SettingController::class, 'updateNoteValue'])->name('update.note.value');

    // Profile route
    Route::get('profile', [UserController::class, 'profile'])->name('profile');
    Route::post('edit-profile', [UserController::class, 'editprofile'])->name('update.account');
    Route::put('user-password-update', [UserController::class, 'password_update'])->name('user.password.update');
    Route::get('user-list', [UserController::class, 'list'])->name('user.list');

    // Theme Preview Routes
    Route::resource('theme-preview', ThemeSettingController::class);

    Route::post('theme-preview/save-theme-layout', [ThemeSettingController::class, 'saveThemeLayout'])->name('save-theme-layout');
    Route::post('theme-preview/publish-theme', [ThemeSettingController::class, 'publishTheme'])->name('publish-theme');
    Route::post('home-page-setting', [ThemeSettingController::class, 'pageSetting'])->name('home.page.setting');
    Route::get('langing-page-setting', [ThemeSettingController::class, 'landingPageSetting'])->name('langing.page.setting');
    Route::post('theme-preview/sidebar-option', [ThemeSettingController::class, 'sidebarOption'])->name('sidebar-option');
    Route::post('theme-preview/make-active', [ThemeSettingController::class, 'makeActiveTheme'])->name('theme-preview.make-active');

    Route::get('theme-preview-content/{theme_id?}', [ThemeSettingController::class, 'themeCustomize'])->name('theme-preview-content');

    Route::resource('addon', AddonController::class);
    Route::get('addon-apps', [AddonController::class, 'AddonApps'])->name('addon.apps');
    Route::post('theme-addon', [AddonController::class, 'ThemeInstall'])->name('addon.theme');
    Route::post('theme-enable', [AddonController::class, 'ThemeEnable'])->name('theme.enable');

    // Report
    Route::resource('reports', ReportController::class);
    Route::get('reports-chart', [ReportController::class, 'reports_chart'])->name('reports.chart');
    Route::get('report-export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('order-reports', [ReportController::class, 'OrderReport'])->name('reports.order_report');
    Route::get('order-reports-chart', [ReportController::class, 'order_reports_chart'])->name('reports.order.chart');
    Route::get('order-report-export', [ReportController::class, 'order_report_export'])->name('order.reports.export');
    Route::get('order-barchart-report-export', [ReportController::class, 'order_bar_report_export'])->name('order.barchart.reports.export');
    Route::get('order-reports-data', [ReportController::class, 'BarChartOrderReport'])->name('orders.barchart_order_report');
    Route::get('order-reports-chart-data', [ReportController::class, 'order_reports_chart_data'])->name('reports.order.chart.data');
    Route::get('stock-reports', [ReportController::class, 'StockReport'])->name('reports.stock_report');
    Route::get('/stock-report', [ReportController::class, 'StockReport'])->name('stock.report');

    Route::get('product-order-sale-reports', [ReportController::class, 'OrderSaleProductReport'])->name('reports.order_product_report');
    Route::get('product-order-reports', [ReportController::class, 'order_product_reports'])->name('reports.product.order.chart');
    Route::get('product-order-export', [ReportController::class, 'product_order_export'])->name('product.order.export');
    Route::get('category-order-sale-reports', [ReportController::class, 'OrderSaleCategoryReport'])->name('reports.order_category_report');
    Route::get('category-order-reports', [ReportController::class, 'order_category_reports'])->name('reports.category.order.chart');
    Route::get('category-order-export', [ReportController::class, 'category_order_export'])->name('category.order.export');
    Route::get('order-downloadable-reports', [ReportController::class, 'OrderDownlodableReport'])->name('reports.order_downloadable_report');

    Route::get('Brand-order-sale-reports', [ReportController::class, 'OrderSaleBrandReport'])->name('reports.order_brand_report');
    Route::get('brand-order-reports', [ReportController::class, 'order_brand_reports'])->name('reports.brand.order.chart');
    Route::get('brand-order-export', [ReportController::class, 'brand_order_export'])->name('brand.order.export');

    Route::get('top-all-reports', [ReportController::class, 'top_product'])->name('reports.top_product');
    Route::get('Status-reports', [ReportController::class, 'showOrderStatusReport'])->name('reports.orderStatusReport');
    Route::get('order-country-reports', [ReportController::class, 'showCountryOrderReport'])->name('reports.orderCountryReport');

    Route::resource('wishlist', WishlistController::class);
    Route::post('abandon-wish-emailsend', [WishlistController::class, 'abandon_wish_emailsend'])->name('wish.emailsend');
    Route::post('abandon-wishlist-messagesend', [WishlistController::class, 'abandon_wishlist_messsend'])->name('wishlist.messagesend');

    Route::post('whatsapp-notification', [SettingController::class, 'whatsapp_notification_setting'])->name('whatsapp-notification');
    Route::get('update-whatsapp-notification', [SettingController::class, 'whatsapp_notification'])->name('update.whatsapp.notification');
    //send test whatsapp message
    Route::post('whatsapp-massage-test', [SettingController::class, 'Testwhatsappmassage'])->name('whatsappmassage.test');

    Route::post('whatsapp-send-massage', [SettingController::class, 'testSendwhatsappmassage'])->name('whatsapp.send.massage');

    // abandon carts handled
    Route::get('abandon-carts-handled', [CartController::class, 'abandon_carts_handled'])->name('abandon.carts.handled');
    Route::get('abandon-carts-show/{cartId}', [CartController::class, 'abandon_carts_show'])->name('carts.show');
    Route::delete('abandon-carts-destroy/{cartId}', [CartController::class, 'abandon_carts_destroy'])->name('carts.destroy');
    Route::post('abandon-carts-emailsend', [CartController::class, 'abandon_carts_emailsend'])->name('carts.emailsend');

    Route::post('abandon-carts-messagesend', [CartController::class, 'abandon_carts_messsend'])->name('carts.messagesend');
    Route::post('abandon-wishlist-messagesend', [WishlistController::class, 'abandon_wishlist_messsend'])->name('wishlist.messagesend');

    Route::post('abandon-wish-emailsend', [WishlistController::class, 'abandon_wish_emailsend'])->name('wish.emailsend');


    //support-ticket
    Route::resource('support_ticket', SupportTicketController::class);
    Route::get('ticket-view/{id}', [SupportTicketController::class, 'show'])->name('support_ticket.view');
    Route::post('ticket/{id}/conversion', [SupportTicketController::class, 'conversion_store'])->name('conversion.store');
    Route::post('ticket-status-change/{id}', [SupportTicketController::class, 'ticket_status_change'])->name('support_ticket.status.change');

    //Question-answer
    Route::resource('product-question', ProductQuestionController::class);

    // AI tool Route
    Route::get('generate/{template_name}', [AITemplateController::class, 'create'])->name('generate');
    Route::post('generate/keywords/{id}', [AITemplateController::class, 'getKeywords'])->name('generate.keywords');
    Route::post('generate/response', [AITemplateController::class, 'aiGenerate'])->name('generate.response');

    // Customer Route
    Route::resource('customer', CustomerController::class);
    Route::get('/customer-filter', [CustomerController::class, 'CustomFilter'])->name('customer.filter');
    Route::get('/customer-filter-data', [CustomerController::class, 'CustomFilterData'])->name('customer.filter.data');
    Route::get('/customer-status', [CustomerController::class, 'customerStatus'])->name('update.customer.status');
    Route::get('/customer-timeline/{id}', [CustomerController::class, 'customerTimeline'])->name('customer.timeline');
    
    Route::get('customer-grid', [CustomerController::class, 'customer_grid_view'])->name('customer.grid');

    // Order Route
    Route::get('order/export', [OrderController::class, 'fileExport'])->name('order.export');
    Route::resource('order', OrderController::class);
    Route::get('order/all', [OrderController::class, 'allOrders']);
    Route::get('order-view/{id}', [OrderController::class, 'order_view'])->name('order.view');

    Route::get('order-grid', [OrderController::class, 'order_grid_view'])->name('order.grid');
    // Pos Route
    Route::resource('pos', PosController::class);
    Route::get('product-categories', [MainCategoryController::class, 'getProductCategories'])->name('product.categories');
    Route::get('search-products', [ProductController::class, 'searchProducts'])->name('search.products');
    Route::get('search-products-sku', [ProductController::class, 'searchProductsSku'])->name('search.products.sku');
    Route::post('/cartdiscount', [PosController::class, 'cartDiscount'])->name('cartdiscount');
    Route::get('addToCart/{id}/{session}/{variation_id?}', [ProductController::class, 'addToCart'])->name('pos.add.to.cart');
    Route::patch('update-cart', [ProductController::class, 'updateCart']);
    Route::delete('remove-from-cart', [ProductController::class, 'removeFromCart'])->name('remove-from-cart');
    Route::get('printview/pos', [PosController::class, 'printView'])->name('pos.printview');
    Route::get('pos/data/store', [PosController::class, 'store'])->name('pos.data.store');
    Route::post('empty-cart', [ProductController::class, 'emptyCart'])->name('empty-cart');
    // POS variant Route
    Route::get('pos/product-variant/{id}/{session}', [ProductController::class, 'productVariant'])->name('pos.product.variant');
    Route::get('pos/add-cart-variant/{id}/{session}/{variation_id?}', [ProductController::class, 'addToCartVariant'])->name('addToCartVariant');
    Route::get('get-products-variant-quantity', [ProductController::class, 'getProductsVariantQuantity'])->name('get.products.variant.quantity');

    Route::resource('contacts', ContactController::class)->except(['store']);
    Route::get('{theme_name}/contact-us', [HomeController::class, 'contactUs'])->name('contact.us');

    // Tag Route
    Route::resource('tag', TagController::class);

    Route::get('change-language/{lang}', [LanguageController::class, 'changeLanquage'])->name('change.language');
    Route::get('manage-language/{lang?}/{module?}', [LanguageController::class, 'manageLanguage'])->name('manage.language');
    Route::post('store-language-data/{lang?}/{module?}', [LanguageController::class, 'storeLanguageData'])->name('store.language.data');
    Route::get('create-language', [LanguageController::class, 'createLanguage'])->name('create.language');
    Route::post('store-language', [LanguageController::class, 'storeLanguage'])->name('store.language');
    Route::delete('/lang/{lang}', [LanguageController::class, 'destroyLang'])->name('lang.destroy');



    Route::post('disable-language', [LanguageController::class, 'disableLang'])->name('disablelanguage');

    Route::post('product-image-delete', [AppSettingController::class, 'image_delete'])->name('product.image.delete');
    Route::post('firebase-settings', [AppSettingController::class, 'FirebaseSettings'])->name('firebase.settings');


    // Module Install
    Route::get('modules/list', [ModuleController::class, 'index'])->name('module.index');
    Route::post('modules-enable', [ModuleController::class, 'enable'])->name('module.enable');
    Route::post('remove-modules/{module}', [ModuleController::class, 'remove'])->name('module.remove');
    Route::get('modules/add', [ModuleController::class, 'add'])->name('module.add');
    Route::post('install-modules', [ModuleController::class, 'install'])->name('module.install');

    // Product Brand
    Route::resource('product-brand', ProductBrandController::class);
    Route::get('product-brand-status', [ProductBrandController::class, 'changeStatus'])->name('product-brand.status');
    Route::get('product-brand-popular', [ProductBrandController::class, 'changePopular'])->name('product-brand.popular');

    // Product Label
    Route::resource('product-label', ProductLabelController::class);
    Route::get('product-label-status', [ProductLabelController::class, 'changeStatus'])->name('product-label.status');

    Route::get('newsletter/export', [NewsletterController::class, 'fileExport'])->name('newsletter.export');

    Route::resource('currency', CurrencyController::class)->except(['edit']);
    Route::get('/currency/{id}/edit', [CurrencyController::class, 'edit'])->name('currency.edit');
  });

  Route::post('contacts/{slug?}', [ContactController::class, 'store'])->name('contacts.store');
  Route::get('{storeSlug}/contact_us', [HomeController::class, 'contactUs'])->name('page.contact_us')->middleware('themelanguage');
  Route::get('{storeSlug}/collections/{list}', [HomeController::class, 'product_page'])->name('collections.all')->middleware('themelanguage');
  Route::get('change-language-store/{lang}', [LanguageController::class, 'changeLanquageStore'])->name('change.languagestore');
  Route::get('{storeSlug}/privacy-policy', [HomeController::class, 'privacy_page'])->name('privacy_page')->middleware('themelanguage');

  Route::resource('newsletter', NewsletterController::class)->middleware('themelanguage');
  Route::get('shippinglabel/pdf/{id}', [OrderController::class, 'shippinglabel'])->name('shippinglabel.pdf');
  Route::post('order-return', [OrderController::class, 'order_return'])->name('order.return')->middleware('themelanguage');
  Route::get('order-view/{id}', [OrderController::class, 'order_view'])->name('order.view')->middleware('themelanguage');
  Route::get('orders/order_view/{id}', [OrderController::class, 'show'])->name('order.order_view')->middleware('themelanguage');
  Route::get('order-receipt/{id}', [OrderController::class, 'order_receipt'])->name('order.receipt')->middleware('themelanguage');
  Route::post('order-status-change/{id}', [OrderController::class, 'order_status_change'])->name('order.status.change')->middleware('themelanguage');
  Route::post('order-payment-status', [OrderController::class, 'order_payment_status'])->name('order.payment.status')->middleware('themelanguage');
  Route::resource('order-note', OrderNoteController::class);
  Route::get('{storeSlug}/order/{id}', [OrderController::class, 'orderdetails'])->name('order.details')->middleware('themelanguage');
  Route::post('update-order-status/{id}', [OrderController::class, 'updateStatus'])->name('order.order_status_update')->middleware('themelanguage');

  Route::post('order-return-request', [OrderController::class, 'order_return_request'])->name('order.return.request')->middleware('themelanguage');
  Route::get('order-view/{id}', [OrderController::class, 'order_view'])->name('order.view')->middleware('themelanguage');
  Route::get('{storeSlug?}/customerorder/{id}', [AccountProfileController::class, 'customerorder'])->name('customer.order')->middleware('themelanguage');
  Route::post('{storeSlug}/downloadable_prodcut', [AccountProfileController::class, 'downloadable_prodcut'])->name('user.downloadable_prodcut')->middleware('themelanguage');
  Route::get('{storeSlug}/order-refund/{id}', [AccountProfileController::class, 'order_refund'])->name('order.refund')->middleware('themelanguage');
  Route::post('{storeSlug}/order-refund-request/{id}', [AccountProfileController::class, 'order_refund_request'])->name('order.refund.request')->middleware('themelanguage');
  Route::post('update-refund-status', [OrderRefundController::class, 'updateRefundStatus'])->name('update-refund-status')->middleware('themelanguage');
  Route::post('cancel-refund-status', [OrderRefundController::class, 'CancelRefundStatus'])->name('cancel.refund.status')->middleware('themelanguage');
  Route::post('refund-stock', [OrderRefundController::class, 'RefundStock'])->name('refund.stock')->middleware('themelanguage');
  Route::post('/update-final-price/{id}', [OrderRefundController::class, 'updateFinalPrice'])->name('updateFinalPrice')->middleware('themelanguage');
  Route::post('/refund-amount/{id}', [OrderRefundController::class, 'RefundAmonut'])->name('refund.amount')->middleware('themelanguage');

  Route::resource('refund-request', OrderRefundController::class)->middleware('themelanguage');
  Route::post('refund-settings', [OrderRefundController::class, 'updateStatus'])->name('refund.settings')->middleware('themelanguage');

  Route::post('product-page-setting', [AppSettingController::class, 'product_page_setting'])->name('product.page.setting');
  Route::middleware([ActiveTheme::class, 'themelanguage'])->group(function () {

    Route::get('/{storeSlug}/product-list', [HomeController::class, 'product_page'])->name('page.product-list');
    Route::get('{storeSlug}/product-filter', [HomeController::class, 'product_page_filter'])->name('product.page.filter');
    Route::get('/{storeSlug}/product/{product_slug}', [HomeController::class, 'product_detail'])->name('page.product');
    Route::post('{storeSlug}/product_price', [ProductController::class, 'product_price'])->name('product.price');

    Route::get('{storeSlug}/login', [CustomerLoginController::class, 'showLoginForm'])->name('customer.login');
    Route::post('{storeSlug}/login/{cart?}', [CustomerLoginController::class, 'login'])->name('customer.login.save');

    Route::get('{storeSlug}/register/{ref_id?}', [CustomerLoginController::class, 'register'])->name('customer.register');
    Route::post('{storeSlug}/register-data', [CustomerLoginController::class, 'registerData'])->name('customer.registerdata');

    Route::get('{storeSlug}/forgot-password', [CustomerLoginController::class, 'forgotPasswordForm'])->name('customer.password.request');
    Route::post('{storeSlug}/forgot-password', [CustomerLoginController::class, 'forgotPassword'])->name('customer.password.email');

    Route::get('{storeSlug}/reset-password', [CustomerLoginController::class, 'resetPasswordForm'])->name('customer.password.reset');
    Route::post('{storeSlug}/reset-password', [CustomerLoginController::class, 'resetPassword'])->name('customer.password.update');


    Route::post('{storeSlug}/customer-logout', [CustomerLoginController::class, 'logout'])->name('customer.logout');
    Route::post('{storeSlug}/product_cart', [CartController::class, 'product_cartlist'])->name('product.cart');
    Route::post('{storeSlug}/change-cart', [CartController::class, 'change_cart'])->name('change.cart');

    Route::get('{storeSlug}/faq', [HomeController::class, 'faqs_page'])->name('page.faq');
    Route::get('/{storeSlug}/blog', [HomeController::class, 'blog_page'])->name('page.blog');
    Route::any('{storeSlug}/blogs/filter/view', [BlogController::class, 'blog_filter'])->name('blogs.filter.view');
    Route::get('{storeSlug}/article/{id}', [HomeController::class, 'article_page'])->name('page.article');
    Route::any('{storeSlug}/cart-list-sidebar', [CartController::class, 'cart_list_sidebar'])->name('cart.list.sidebar');
    Route::get('{storeSlug}/page/cart', [HomeController::class, 'cart_page'])->name('page.cart');
    Route::any('{storeSlug}/checkout', [HomeController::class, 'checkout'])->name('checkout');
    Route::post('{storeSlug}/place-order', [OrderController::class, 'place_order'])->name('place.order');
    Route::post('{storeSlug}/get-shipping-data', [CartController::class, 'get_shipping_data'])->name('get.shipping.data');
    Route::post('{storeSlug}/shipping-method', [CartController::class, 'get_shipping_method'])->name('shipping.method');
    Route::post('{storeSlug}/applycoupon', [OrderController::class, 'applycoupon'])->name('applycoupon');
    Route::get('{storeSlug}/paymentlist', [OrderController::class, 'paymentlist'])->name('paymentlist');
    Route::get('{storeSlug}/additionalnote', [OrderController::class, 'additionalnote'])->name('additionalnote');
    Route::post('{storeSlug}/states-list', [AccountProfileController::class, 'states_list'])->name('states.list');
    Route::post('{slug}/product-wishlist', [WishlistController::class, 'product_wishlist'])->name('product.wishlist');

    Route::post('{storeSlug}/process-payment', [PaymentController::class, 'processPayment'])->name('payment.process');

    Route::any('{storeSlug}/get-payment-paytm', [PaymentController::class, 'getProductStatus'])->name('store.payment.paytm');
    Route::any('{storeSlug}/get-payment-iyzico', [PaymentController::class, 'getProductStatus'])->name('store.payment.iyzico');
    Route::any('{storeSlug}/get-payment-aamarpay', [PaymentController::class, 'getProductStatus'])->name('store.payment.aamarpay');
    Route::any('{storeSlug}/get-payment-midtrans', [PaymentController::class, 'getProductStatus'])->name('store.payment.midtrans');
    Route::post('{storeSlug}/get-massage', [PaymentController::class, 'getWhatsappUrl'])->name('get.whatsappurl');
    Route::post('{storeSlug?}/telegram', [PaymentController::class, 'whatsapp'])->name('user.telegram');
    Route::post('{storeSlug?}/whatsapp', [PaymentController::class, 'whatsapp'])->name('user.whatsapp');

    Route::post('custom-msg/{slug?}', [SettingController::class, 'customMassage'])->name('customMassage');

    Route::get('{storeSlug}/order-summary', [OrderController::class, 'order_summary'])->name('order.summary');
    Route::post('{storeSlug}/place-order-guest', [PaymentController::class, 'place_order_guest'])->name('place.order.guest');
    Route::get('{storeSlug}/store-payment-stripe', [PaymentController::class, 'getProductStatus'])->name('store.payment.stripe');
    Route::post('{storeSlug}/order-khalti', [KhaltiPaymnetController::class, 'getOrderPaymentStatus'])->name('order.khalti');
    Route::any('{storeSlug}/authorizenet-status', [AuthorizeNetPaymnetController::class, 'getOrderPaymentStatus'])->name('order.get.authorizenet.status');

    Route::any('{storeSlug}/store-payment-phonepe', [PaymentController::class, 'getProductStatus'])->name('store.payment.phonepe');

    Route::any('{storeSlug}/get-payment-paiementpro', [PaiementProPaymentController::class, 'getProductStatus'])->name('store.payment.paiementpro');

    Route::any('{storeSlug}/get-payment-cinetPay', [CinetPayController::class, 'getProductStatus'])->name('store.payment.cinetpay');
    Route::any('{storeSlug}/return-payment-cinetPay', [CinetPayController::class, 'returnBackToStore'])->name('store.payment.cinetpay.return');

    Route::any('{storeSlug}/get-payment-easebuzz', [EasebuzzController::class, 'getProductStatus'])->name('store.payment.easebuzz');

    // order track
    Route::post('{storeSlug}/order-track', [HomeController::class, 'order_track'])->name('order.track');


    //Question-answer
    Route::get('/{storeSlug}/question/{id}', [ProductQuestionController::class, 'Question'])->name('question');
    Route::get('/{storeSlug}/more_question/{id}', [ProductQuestionController::class, 'more_question'])->name('more_question');
    Route::post('/{storeSlug}/product-question', [ProductQuestionController::class, 'product_question'])->name('product_question');


    Route::post('add-newsletter', [AccountProfileController::class, 'add_newsletter'])->name('add.newsletter');
    Route::get('{storeSlug}/add-adress-form', [AccountProfileController::class, 'add_address_form'])->name('add.address.form');
    Route::post('{storeSlug}/add-address', [AccountProfileController::class, 'add_address'])->name('add.address');
    Route::get('{storeSlug}/addressbook', [AccountProfileController::class, 'addressbook'])->name('addressbook');
    Route::get('{storeSlug}/get-addressbook-data', [AccountProfileController::class, 'get_addressbook_data'])->name('get.addressbook.data');
    Route::get('{storeSlug}/edit-address-form', [AccountProfileController::class, 'edit_address_form'])->name('edit.address.form');
    Route::post('{storeSlug}/update-addressbook-data/{id}', [AccountProfileController::class, 'update_addressbook_data'])->name('update.addressbook.data');
    Route::get('{storeSlug}/delete-addressbook', [AccountProfileController::class, 'delete_addressbook'])->name('delete.addressbook');
    Route::get('{storeSlug}/delete-wishlist', [AccountProfileController::class, 'delete_wishlist'])->name('delete.wishlist');

    Route::get('{storeSlug}/order-list', [AccountProfileController::class, 'order_list'])->name('order.list');
    Route::get('{storeSlug}/reward-list', [AccountProfileController::class, 'reward_list'])->name('reward.list');
    Route::get('{storeSlug}/order-return-list', [AccountProfileController::class, 'order_return_list'])->name('order.return.list');
    Route::post('{storeSlug}/wish-list', [AccountProfileController::class, 'wish_list'])->name('wish.list');
    Route::post('{storeSlug}/wish-list-sidebar', [WishlistController::class, 'wish_list_sidebar'])->name('wish.list.sidebar');

    Route::post('{storeSlug}/city-list', [AccountProfileController::class, 'city_list'])->name('city.list');
    Route::post('{slug}/cart-remove', [CartController::class, 'cart_remove'])->name('cart.remove');
    Route::post('{storeSlug}/customer_password_change', [AccountProfileController::class, 'password_change'])->name('customer.password.change');
    Route::post('{storeSlug}/profile-update', [AccountProfileController::class, 'profile_update'])->name('profile.update');
    Route::resource('{storeSlug}/my-account', AccountProfileController::class);

    //support ticket
    Route::get('{storeSlug}/support-ticket', [AccountProfileController::class, 'support_ticket'])->name('support.ticket');
    Route::get('{storeSlug}/add-ticket', [AccountProfileController::class, 'add_support_ticket'])->name('add.support.ticket');
    Route::post('{storeSlug}/store-ticket', [AccountProfileController::class, 'support_ticket_store'])->name('support.ticket.store');
    Route::get('{storeSlug}/destroy-ticket/{eid}', [AccountProfileController::class, 'destroy_support_ticket'])->name('destroy.ticket');

    Route::get('{storeSlug}/get-support-ticket/{id}', [AccountProfileController::class, 'edit_support_ticket'])->name('get.support.ticket');
    Route::post('{storeSlug}/update-support-ticket/{eid}', [AccountProfileController::class, 'update_support_ticket'])->name('update.support.ticket');
    Route::get('{storeSlug}/reply-support-ticket/{id}', [AccountProfileController::class, 'reply_support_ticket'])->name('reply.support.ticket');

    Route::post('{storeSlug}/support-ticket/{tid}', [AccountProfileController::class, 'ticket_reply'])->name('ticket.reply');

    Route::delete('{storeSlug}/ticket-attachment/{tid}/destroy/{id}', [AccountProfileController::class, 'attachmentDestroy'])->name('tickets.attachment.destroy');

    Route::get('{storeSlug}/search-product', [HomeController::class, 'search_products'])->name('search.product');
    Route::post('/{storeSlug}/status-cancel', [OrderController::class, 'status_cancel'])->name('status.cancel');
    Route::post('{storeSlug}/get-tax-data', [CartController::class, 'get_tax_data'])->name('get.tax.data');
    Route::get('/{storeSlug}/home', [HomeController::class, 'landing_page'])->name('landing_page')->middleware('themelanguage');
    Route::get('/{storeSlug}/404', [HomeController::class, 'pageError'])->name('theme.404')->middleware('themelanguage');

    Route::get('/{storeSlug}/custom/{slug}', [ThemeSettingController::class, 'page'])->where('slug', '.*')->name('themes.page');
    Route::get('/{storeSlug}/custom/{slug}', [ThemeSettingController::class, 'page'])->where('slug', '.*')->name('theme.page');
    Route::get('/{storeSlug}/custom/{slug}', [ThemeSettingController::class, 'page'])->where('slug', '.*')->name('custom.page');
    //senagepay
    Route::post('/plan/company/senangpay', [SenangPayController::class, 'planPayWithSenangpay'])->name('plan.pay.with.senagapay');
    Route::any('/senang-pay/call_back', [SenangPayController::class, 'paymentCallback'])->name('senangpay.call_back');
    Route::any('{storeSlug}/get-payment-senang-pay', [PaymentController::class, 'getProductStatus'])->name('store.payment.senang-pay');

    //CyberSource
    Route::post('plan-pay-with-cybersource', [CyberSourceController::class, 'planPayWithCyberSource'])->name('plan.pay.with.cybersource');
    Route::any('plan-get-cybersource-status', [CyberSourceController::class, 'planPayWithCyberSourceData'])->name('plan.get.cybersource.status');
    Route::any('store-get-cybersource-status', [CyberSourceController::class, 'storePayWithCyberSourceData'])->name('store.get.cybersource.status');
    Route::any('{storeSlug}/get-payment-CyberSource', [PaymentController::class, 'getProductStatus'])->name('store.payment.CyberSource');

    //ozow
    Route::post('plan-pay-with/ozow', [OzowController::class, 'planPayWithOzow'])->name('plan.pay.with.ozow');
    Route::get('plan-get-ozow-status/{plan_id}', [OzowController::class, 'planGetOzowStatus'])->name('plan.get.ozow.status');
    Route::any('store-get-ozow-status', [OzowController::class, 'storeGetOzowStatus'])->name('store.get.ozow.status');
    Route::any('{storeSlug}/get-payment-ozow', [PaymentController::class, 'getProductStatus'])->name('store.payment.ozow');


    Route::any('{storeSlug}/get-payment-NMI', [NMIPayController::class, 'getNMIProductStatus'])->name('product-order.pay.with.nmi');

    //PayU
    Route::any('/store-payu-payment/status', [PayUPaymentController::class, 'storeGetPayUStatus'])->name('store.payu.status');
    Route::any('{storeSlug}/get-payment-payu', [PaymentController::class, 'getProductStatus'])->name('store.payment.payu');

    //new theme
    Route::get('{storeSlug}/make-checkout/{id}', [OfertemagController::class, 'MakeCheckout'])->name('make.checkout');
    Route::post('{storeSlug}/ofertemag-place-order-guest', [OfertemagController::class, 'ofertemag_place_order_guest'])->name('ofertemag.place.order.guest');
    Route::post('payment-cities-list', [OfertemagController::class, 'cities_list'])->name('payment.cities.list');
    Route::get('{storeSlug}/ofertemag-order-summary/{order_id}', [OfertemagController::class, 'ofertemag_order_summary'])->name('ofertemag.order.summary');
    Route::any('{storeSlug}/ofertemag-order-save', [OfertemagController::class, 'SaveOrder'])->name('ofertemag.order.save');
    Route::post('{storeSlug}/product-cart-list', [OfertemagController::class, 'Product_cart_list'])->name('ofertenag.cart.list');
    // Paynow
    Route::any('store-get-Paynow-status', [PaynowController::class, 'storeGetPaynowStatus'])->name('store.get.Paynow.status');
    Route::any('{storeSlug}/get-payment-Paynow', [PaymentController::class, 'getProductStatus'])->name('store.payment.Paynow');

    //MyFatoorah
    Route::any('/myfatoorah/store_call_back', [MyFatoorahController::class, 'paymentCallback'])->name('store.myfatoorah.call_back');
    Route::any('{storeSlug}/get-payment-myfatoorah', [PaymentController::class, 'getProductStatus'])->name('store.payment.myfatoorah');

    // ESewa
    Route::post('{storeSlug}/transaction-with-esewa', [ESewaPaymentController::class, 'Transactionfailure'])->name('esewa.transaction.failure');

    // DPO
    Route::any('store-pay-with/DPO/create', [DPOPayController::class, 'create'])->name('store.dpo.view');
    Route::any('store-pay-with/DPO', [DPOPayController::class, 'storePayWithDPO'])->name('store.pay.with.dpo');
    Route::any('store-get-DPO-status', [DPOPayController::class, 'storeGetDPOStatus'])->name('store.get.dpo.status');
    Route::any('{storeSlug}/DPO-payment-DPO', [PaymentController::class, 'getProductStatus'])->name('store.payment.dpo');

    //Braintree
    Route::post('/store/payment/status', [BraintreeController::class, 'storeGetBraintreeStatus'])->name('store.braintree.status');
    Route::any('{storeSlug}/get-payment-braintree', [PaymentController::class, 'getProductStatus'])->name('store.payment.braintree');
    Route::get('store/pay', [BraintreeController::class, 'pay'])->name('store.braintree.pay');

    // Powertranz
    Route::any('store-pay-with/Powertranz/create', [PowertranzPaymentController::class, 'create'])->name('store.Powertranz.view');
    Route::any('store-pay-with/Powertranz', [PowertranzPaymentController::class, 'storePayWithPowertranz'])->name('store.pay.with.Powertranz');
    Route::any('store-get-Powertranz-status/{storeSlug}', [PowertranzPaymentController::class, 'storeGetPowertranzStatus'])->name('store.get.Powertranz.status');
    Route::any('{storeSlug}/get-payment-Powertranz', [PaymentController::class, 'getProductStatus'])->name('store.payment.Powertranz');

    // SSLCommerz
    Route::any('{storeSlug}/get-payment-sslcommerz', [PaymentController::class, 'getProductStatus'])->name('store.payment.sslcommerz');

    //  Route::get('/{storeSlug}', [HomeController::class, 'landing_page'])->name('landing_page')->middleware('themelanguage');
  });

  Route::get('/pay/{pay}', [PayUPaymentController::class, 'pay'])->name('payu.pay');

});