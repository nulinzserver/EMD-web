<?php

use App\Http\Controllers\Api\ClientRegistrationController;
use App\Http\Controllers\Api\ClientSyncController;
use Illuminate\Support\Facades\Route;

// create the notification for the emd deposit received amount amount
Route::post('/update_popup', [ClientSyncController::class, 'update_popup']);

// With Gst Signup
Route::post('verify-gst', [ClientRegistrationController::class, 'verifyGst']);
Route::post('verify-otp', [ClientRegistrationController::class, 'verifyOtp']);
Route::post('set-password', [ClientRegistrationController::class, 'setPassword']);
Route::post('complete-registration', [ClientRegistrationController::class, 'completeRegistration']);
Route::post('upload-profile-image', [ClientRegistrationController::class, 'uploadProfileImage']);
Route::post('/resend-otp', [ClientRegistrationController::class, 'resendOtp']);

// Without GST SignUp
Route::post('verify-withoutgst', [ClientRegistrationController::class, 'storeClientDetails']);
Route::post('verifymobile_otp', [ClientRegistrationController::class, 'verifyMobileOtp']);
Route::post('set_password_mobile', [ClientRegistrationController::class, 'setPasswordMobile']);

Route::post('/scheme-list', [ClientRegistrationController::class, 'schemeList']);
Route::post('/location-list', [ClientRegistrationController::class, 'locationList']);

// SideBar
Route::post('edit-profile', [ClientRegistrationController::class, 'editProfile']);
Route::post('get-profile', [ClientRegistrationController::class, 'getProfile']);
Route::post('update-profile-phone', [ClientRegistrationController::class, 'updateProfilePhone']);

Route::post('add-user', [ClientRegistrationController::class, 'addUser']);
Route::post('list-users', [ClientRegistrationController::class, 'listUsers']);
Route::post('edit-user', [ClientRegistrationController::class, 'editUser']);
Route::post('update-user', [ClientRegistrationController::class, 'updateUser']);
Route::post('update-user-status', [ClientRegistrationController::class, 'updateUserStatus']);
Route::post('change-password', [ClientRegistrationController::class, 'changePassword']);
Route::post('dashboard-stats', [ClientRegistrationController::class, 'dashboardStats']);
Route::post('create-subscription', [ClientRegistrationController::class, 'addSubscription']);
Route::post('getSubscriptionStatus', [ClientRegistrationController::class, 'getSubscriptionStatus']);
Route::post('/signature/upload', [ClientRegistrationController::class, 'uploadSignature']);
Route::post('/signature/get', [ClientRegistrationController::class, 'getSignature']);
Route::post('/signature/update', [ClientRegistrationController::class, 'updateSignature']);

// forgot password
Route::post('forgot-password/send-otp', [ClientRegistrationController::class, 'forgotPasswordSendOtp']);
Route::post('forgot-password/verify-otp', [ClientRegistrationController::class, 'forgotPasswordVerifyOtp']);
Route::post('forgot-password/reset', [ClientRegistrationController::class, 'forgotPasswordReset']);

// Client Sync
Route::post('client-sync/verify-gst', [ClientSyncController::class, 'verifyGstSync']);
Route::post('client-sync/add-nickname', [ClientSyncController::class, 'addNickName']);
Route::get('client-sync/{syncId}', [ClientSyncController::class, 'getClientSync']);
Route::post('/clients/nicknames', [ClientSyncController::class, 'getClientNickNames']);
Route::post('getSyncedClients', [ClientSyncController::class, 'getSyncedClients']);

// ClientWithGST Sync
Route::post('client-sync/save_client_phone', [ClientSyncController::class, 'saveClientByPhone']); // save clinet without gst
Route::match(['get', 'post'], 'client-sync/edit_client/{client_id?}', [ClientSyncController::class, 'edit_client']);

Route::get('/clientsync/{mc_db_id}', [ClientSyncController::class, 'getByDbId']);

// signin
Route::post('/login', [ClientSyncController::class, 'login']);
Route::post('/logout', [ClientSyncController::class, 'logout']);

// Tender Routes
Route::post('/tender/create', [ClientSyncController::class, 'createTender']);
Route::post('/tender/list', [ClientSyncController::class, 'getTenders']);
Route::post('/tender', [ClientSyncController::class, 'getTenderById']);
Route::post('/user_scheme_list', [ClientSyncController::class, 'userSchemeList']);
Route::post('/user_authority_list', [ClientSyncController::class, 'userAuthorityList']);
Route::post('/user_status_list', [ClientSyncController::class, 'userStatusList']);

Route::post('/tender/report/download', [ClientSyncController::class, 'tenderReportDownload']);

Route::post('/user_payment_list', [ClientSyncController::class, 'userPaymentList']);

Route::post('/tender/payment/download', [ClientSyncController::class, 'tenderPaymentDownload']);

Route::post('/tender/refund/report/download', [ClientSyncController::class, 'tenderRefundReportDownload']);

Route::post('tender-profile', [ClientSyncController::class, 'getTenderProfile']);
Route::post('tender/update-status', [ClientSyncController::class, 'updateTenderStatus']);
Route::post('/tender/edit', [ClientSyncController::class, 'getTenderForEdit']);
Route::post('/tender/update', [ClientSyncController::class, 'updateTender']);
Route::post('/tender/delete', [ClientSyncController::class, 'deleteTender']); // Optional
Route::post('update-fcm-token', [ClientSyncController::class, 'updateFcmToken']);

Route::post('/tender-collection/create', [ClientSyncController::class, 'createTenderCollection']);
Route::post('/tender-collection/list', [ClientSyncController::class, 'getTenderCollections']);
Route::get('/tender-collection/by-tender/{tenderId}', [ClientSyncController::class, 'getCollectionsByTender']);
Route::get('/tender-collection/{collectionId}', [ClientSyncController::class, 'getCollectionById']);
Route::post('/tender-collection/update/{collectionId}', [ClientSyncController::class, 'updateTenderCollection']);

// Tender Expense Routes
Route::post('/tender-expense/create', [ClientSyncController::class, 'createTenderExpense']);
Route::post('/tender-expenses', [ClientSyncController::class, 'getTenderExpenses']);
Route::get('/tender/{tenderId}/expenses', [ClientSyncController::class, 'getExpensesByTender']);
Route::get('/tender-expense/{expenseId}', [ClientSyncController::class, 'getExpenseById']);
Route::put('/tender-expense/{expenseId}', [ClientSyncController::class, 'updateTenderExpense']);
Route::delete('/tender-expense/{expenseId}', [ClientSyncController::class, 'deleteTenderExpense']);

// Tender Bill Routes
Route::post('/tender-bill/create', [ClientSyncController::class, 'createTenderBill']); // create tender bill
Route::post('/tender-bills', [ClientSyncController::class, 'getTenderBills']);
Route::post('/tender/bills', [ClientSyncController::class, 'getBillsByTender']);
Route::post('/tender/billbyid', [ClientSyncController::class, 'getBillById']);
// Generate Invoice
Route::post('/invoice/generate', [ClientSyncController::class, 'generateInvoice']);
Route::post('/fetchBillAmount', [ClientSyncController::class, 'fetchBillAmount']);

// Emd remainders
Route::post('/emd-remainder/create', [ClientSyncController::class, 'createEmdRemainder']);
Route::post('/reminders/all', [ClientSyncController::class, 'getAllReminders']);
// Reminder Popup & Actions
Route::post('/reminders/active', [ClientSyncController::class, 'getActiveReminders']);
Route::post('/reminders/mark-seen', [ClientSyncController::class, 'markReminderAsSeen']);
Route::post('/reminders/snooze', [ClientSyncController::class, 'snoozeReminder']);
Route::post('/reminders/dismiss', [ClientSyncController::class, 'dismissReminder']);
Route::get('/reminders/stats', [ClientSyncController::class, 'getReminderStats']);

// for the bill click it will show the data by id
Route::put('/tender-bill/{billId}', [ClientSyncController::class, 'updateTenderBill']);
Route::delete('/tender-bill/{billId}', [ClientSyncController::class, 'deleteTenderBill']);

Route::post('/collect-bill-amount', [ClientSyncController::class, 'collectBillAmount']); // update the status to collect bill

Route::post('/notifications', [ClientSyncController::class, 'listNotifications']);
Route::post('/notifications/mark-read', [ClientSyncController::class, 'markAsRead']);

// create the notification for the emd deposit received amount amount
Route::post('/collect_notify', [ClientSyncController::class, 'collect_notify']);
