<?php

use App\Http\Controllers\admin\AuditorControllerAdmin;
use App\Http\Controllers\admin\AuthControllerAdmin;
use App\Http\Controllers\admin\ContractorControllerAdmin;
use App\Http\Controllers\admin\DashboardControllerAdmin;
use App\Http\Controllers\auditor\AuthControllerAuditor;
use App\Http\Controllers\auditor\DashboardControllerAuditor;
use App\Http\Controllers\auditor\UserProfileControllerAuditor;
use App\Http\Controllers\landing\LandingPageController;
use App\Http\Controllers\web\AccessControllerWeb;
use App\Http\Controllers\web\AuthControllerWeb;
use App\Http\Controllers\web\ClientControllerWeb;
use App\Http\Controllers\web\DashboardControllerWeb;
use App\Http\Controllers\web\ReportControllerWeb;
use App\Http\Controllers\web\TenderControllerWeb;
use App\Http\Controllers\web\UserControllerWeb;
use Illuminate\Support\Facades\Route;

// landing /
Route::get('/', [LandingPageController::class, 'home'])->name('home');


Route::prefix('web')->group(function () {

    // login
    Route::get('/', [AuthControllerWeb::class, 'signin'])->name('login');

    // login submit
    Route::post('/signin', [AuthControllerWeb::class, 'signin_check'])->name('signin_check');

    // forgot
    Route::get('/forgot-pass', [AuthControllerWeb::class, 'forgot_pass'])->name('forgot_pass');
    Route::post('/forot-pass-submit', [AuthControllerWeb::class, 'forgot_pass_submit'])->name('forgot_pass_submit');
    Route::get('/forgot-otp', [AuthControllerWeb::class, 'forgot_otp'])->name('forgot_otp');
    Route::post('/forgot-password-verify', [AuthControllerWeb::class, 'forgot_password_verifyotp'])->name('forgot_password_verifyotp');
    Route::get('/change-pass', [AuthControllerWeb::class, 'change_pass'])->name('change_pass');
    Route::post('/password-reset', [AuthControllerWeb::class, 'forgot_password_reset'])->name('forgot_password_reset');

    // signup with gst
    Route::get('/signup', [AuthControllerWeb::class, 'signup_gst'])->name('signup_gst');
    Route::post('/gst-submit', [AuthControllerWeb::class, 'gst_submit'])->name('gst_submit');
    Route::get('/signup-details', [AuthControllerWeb::class, 'signup_details'])->name('signup_details');
    Route::match(['get', 'post'], '/signup-otp', [AuthControllerWeb::class, 'signup_otp'])->name('signup_otp');
    Route::post('/verify-otp', [AuthControllerWeb::class, 'verify_otp'])->name('verify_otp');
    Route::post('/resend-otp', [AuthControllerWeb::class, 'resend_otp'])->name('resend_otp');
    Route::get('/signup-pass', [AuthControllerWeb::class, 'signup_pass'])->name('signup_pass');
    Route::post('/set-password', [AuthControllerWeb::class, 'set_password'])->name('set_password');
    Route::get('/signup-insights', [AuthControllerWeb::class, 'signup_insights'])->name('signup_insights');
    Route::post('/update-insights', [AuthControllerWeb::class, 'update_insights'])->name('update_insights');

    // signup without gst
    Route::get('/signup-no-gst', [AuthControllerWeb::class, 'signup_no_gst'])->name('signup_no_gst');
    Route::match(['get', 'post'], '/signup-no-gst-detials', [AuthControllerWeb::class, 'signup_no_gst_detials'])->name('signup_no_gst_detials');
    Route::post('/non-gst-submit', [AuthControllerWeb::class, 'non_gst_submit'])->name('non_gst_submit');
    Route::get('/signup-no-gst-otp', [AuthControllerWeb::class, 'signup_no_gst_otp'])->name('signup_no_gst_otp');
    Route::post('/signup-no-set-pass', [AuthControllerWeb::class, 'verify_mobile_otp'])->name('verify_mobile_otp');
    Route::get('/signup-no-set-pass', [AuthControllerWeb::class, 'signup_no_set_pass'])->name('signup_no_set_pass');
    Route::post('/set-password-nogst', [AuthControllerWeb::class, 'set_password_nogst'])->name('set_password_nogst');
    Route::get('/signup-no-insights', [AuthControllerWeb::class, 'signup_no_insights'])->name('signup_no_insights');
    Route::post('/update-no-insights', [AuthControllerWeb::class, 'update_no_insights'])->name('update_no_insights');
    Route::post('/skip/{id}', [AuthControllerWeb::class, 'skip'])->name('skip');
});

Route::prefix('web')->middleware('auth:client')->group(function () {

    Route::post('/logout', [AuthControllerWeb::class, 'logout'])->name('logout');
    // change password
    Route::post('/change-password', [DashboardControllerWeb::class, 'change_password'])->name('change_password');

    // dashoard
    Route::get('/dashboard', [DashboardControllerWeb::class, 'index'])->name('dashboard');
    Route::post('/add-attachment', [DashboardControllerWeb::class, 'add_attach'])->name('add_attach');
    Route::post('/change-password', [DashboardControllerWeb::class, 'change_password'])->name('change_password');
    Route::post('/update-profile', [DashboardControllerWeb::class, 'update_profile'])->name('update_profile');

    // client
    Route::get('/client-list', [ClientControllerWeb::class, 'client_list'])->name('client_list');
    Route::get('/add-client',   [ClientControllerWeb::class, 'add_client'])->name('add_client');
    Route::post('/update-client',   [ClientControllerWeb::class, 'update_client'])->name('update_client');
    Route::post('/gst-verify', [ClientControllerWeb::class, 'verifyGstSync'])->name('verifyGstSync');
    Route::post('/add-nick-name', [ClientControllerWeb::class, 'addNickName'])->name('addNickName');
    Route::post('/add-non-gst', [ClientControllerWeb::class, 'addNonGstClient'])->name('addNonGstClient');
    Route::post('/update-nick-name', [ClientControllerWeb::class, 'update_nick_name'])->name('update_nick_name');
    Route::get('/edit-client',   [ClientControllerWeb::class, 'edit_client'])->name('edit_client');

    // tender
    Route::get('/tender-list', [TenderControllerWeb::class, 'tender_list'])->name('tender_list');
    Route::get('/tender-profile/{id}', [TenderControllerWeb::class, 'tender_profile'])->name('tender_profile');
    Route::get('/edit-tender/{id}', [TenderControllerWeb::class, 'edit_tender'])->name('edit_tender');
    Route::get('/add-tender', [TenderControllerWeb::class, 'add_tender'])->name('add_tender');
    Route::post('/post-tender', [TenderControllerWeb::class, 'post_tender_one'])->name('post_tender_one');
    Route::post('/update-tender', [TenderControllerWeb::class, 'update_tender'])->name('update_tender');
    Route::get('/add-tender-type/{id}', [TenderControllerWeb::class, 'add_tender_two'])->name('add_tender_two');
    Route::post('/post-tender-type', [TenderControllerWeb::class, 'post_tender_two'])->name('post_tender_two');
    Route::get('/edit-tender-type/{id}', [TenderControllerWeb::class, 'edit_tender_two'])->name('edit_tender_two');
    Route::post('/update-tender-two}', [TenderControllerWeb::class, 'update_tender_two'])->name('update_tender_two');
    Route::get('/add-tender-attach/{id}', [TenderControllerWeb::class, 'add_tender_three'])->name('add_tender_three');
    Route::get('/edit-tender-attach/{id}', [TenderControllerWeb::class, 'edit_tender_three'])->name('edit_tender_three');
    Route::post('/post-tender-attach', [TenderControllerWeb::class, 'post_tender_three'])->name('post_tender_three');
    Route::post('/update-tender-attach', [TenderControllerWeb::class, 'update_tender_three'])->name('update_tender_three');
    // Route::post('/invoice/generate', [TenderControllerWeb::class, 'generateInvoice'])->name('generateInvoice');
    Route::post('/create-emd-reminder', [TenderControllerWeb::class, 'createEmdReminderWeb'])->name('createEmdReminderWeb');

    Route::post('/tender-status', [TenderControllerWeb::class, 'tender_status'])->name('tender_status');
    Route::post('/add-expense', [TenderControllerWeb::class, 'add_exp'])->name('add_exp');
    Route::post('/add-billing', [TenderControllerWeb::class, 'add_billing'])->name('add_billing');
    Route::post('/collect-amount', [TenderControllerWeb::class, 'collect_amount'])->name('collect_amount');
    Route::post('/collect_notify', [TenderControllerWeb::class, 'collect_notify'])->name('collect_notify');

    Route::get('/access-list', [AccessControllerWeb::class, 'access_list'])->name('access_list');
    // user 
    Route::get('/add-user', [UserControllerWeb::class, 'add_user'])->name('add_user');
    Route::post('/post-user', [UserControllerWeb::class, 'post_update'])->name('post_update');
    Route::post('/update-user', [UserControllerWeb::class, 'updateUser'])->name('update_user');
    // report
    Route::get('/report', [ReportControllerWeb::class, 'report'])->name('report');
    Route::get('invoice/web/{billId}', [ReportControllerWeb::class, 'generateInvoiceWeb'])->name('invoice.generate');
    // Route::get('/report_pdf', [ReportControllerWeb::class, 'report_pdf'])->name('report_pdf');

    Route::post('/web/report/download', [ReportControllerWeb::class, 'downloadReport'])->name('downloadReport');
});



// admin
Route::prefix('admin')->namespace('App\Http\Controllers\admin')->group(function () {

    // login    
    route::get('/', [AuthControllerAdmin::class, 'signin'])->name('signin_admin');
    route::get('/forgot-pass', [AuthControllerAdmin::class, 'forgot_pass'])->name('forgot_pass_admin');
    route::get('/forgot-otp', [AuthControllerAdmin::class, 'forgot_otp'])->name('forgot_otp_admin');
    Route::get('/changes-pass', [AuthControllerAdmin::class, 'signup_pass'])->name('signup_pass_admin');

    // dashboard
    Route::get('/dashboard', [DashboardControllerAdmin::class, 'index'])->name('dashboard_admin');

    // contractor
    Route::get('/contractor', [ContractorControllerAdmin::class, 'contractor_list'])->name('contractor_list');

    Route::get('/auditor', [AuditorControllerAdmin::class, 'auditor_list'])->name('auditor_list');
});

// auditor
Route::prefix('/auditor')->namespace('App\Http\Controllers\auditor')->group(function () {

    // login    
    route::get('/', [AuthControllerAuditor::class, 'signin'])->name('signin_auditor');
    route::get('/forgot-pass', [AuthControllerAuditor::class, 'forgot_pass'])->name('forgot_pass_auditor');
    route::get('/forgot-otp', [AuthControllerAuditor::class, 'forgot_otp'])->name('forgot_otp_auditor');
    Route::get('/changes-pass', [AuthControllerAuditor::class, 'change_pass'])->name('change_pass_auditor');

    Route::get('/signup', [AuthControllerAuditor::class, 'signup'])->name('signup_auditor');
    Route::get('/signup-otp', [AuthControllerAuditor::class, 'signup_otp'])->name('signup_otp_auditor');
    Route::get('/signup-pass', [AuthControllerAuditor::class, 'signup_pass'])->name('signup_pass_auditor');

    // dashboard
    Route::get('/dashboard', [DashboardControllerAuditor::class, 'dashboard'])->name('dashboard_auditor');

    // userprofile
    Route::get('/user-profile', [UserProfileControllerAuditor::class, 'user_profile'])->name('user_profile_auditor');
});
