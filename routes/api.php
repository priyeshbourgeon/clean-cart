<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TempleController;
use App\Http\Controllers\Api\DeityPoojaStarController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\DevoteeController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\VersionController;

use App\Http\Controllers\Api\Customer\CustomerController;
use App\Http\Controllers\Api\Customer\FranchiseController;
use App\Http\Controllers\Api\Customer\BookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/auth/register', [AuthController::class, 'createUser'])->name('register');
Route::post('/auth/login', [AuthController::class, 'loginUser'])->name('login');
Route::apiResource('branches', TempleController::class);

Route::middleware(['auth:sanctum','CheckDatabase'])->group(function () {
    Route::get('deities', [DeityPoojaStarController::class,'allDieties'])->name('deities');
    Route::get('stars', [DeityPoojaStarController::class,'allStars'])->name('stars');
    Route::get('special-stars', [DeityPoojaStarController::class,'allSpecialStars'])->name('specialStars');
    Route::get('payment-modes', [DeityPoojaStarController::class,'allPaymentModes'])->name('paymentModes');
    Route::get('quick-bill-pooja', [BillingController::class,'quickBillPooja'])->name('quickBillPooja');
	Route::get('staff', [BillingController::class,'staffList'])->name('staff_list');
	Route::get('roles', [StaffController::class,'roles'])->name('roles');
	Route::post('create-user', [StaffController::class,'storeUser'])->name('staff.store');
	
    Route::post('deity/poojas', [DeityPoojaStarController::class,'deityPoojas'])->name('deity.poojas');
    Route::post('counters', [BillingController::class,'counters'])->name('counters');
    Route::post('devotees', [DevoteeController::class,'devotees'])->name('devotees');
    Route::post('create-devotee', [DevoteeController::class,'storeDevotee'])->name('devotees.store');
    Route::post('preview-bill', [BillingController::class,'previewBill'])->name('billing.previewBill');
    Route::post('save-bill', [BillingController::class,'saveBill'])->name('billing.saveBill');
    Route::post('quick-bill', [BillingController::class,'quickBill'])->name('billing.quickBill');

	Route::post('test-preview-bill', [TestController::class,'previewBill'])->name('test.previewBill');
	Route::post('test-bill', [TestController::class,'testBill'])->name('test.testBill');
    
    Route::get('bill-list', [BillingController::class,'billList'])->name('billing.billList');
	Route::post('pending-bill-list',[BillingController::class,'pendingList'])->name('billing.pending-list');
	Route::post('active-bill-list',[BillingController::class,'activeBillList'])->name('billing.active-list');
    Route::post('completed-bill-list',[BillingController::class,'completedBillList'])->name('billing.completed-list');
    
    Route::post('update-bill-status/active', [BillingController::class,'changeCompletionStatusToActive'])->name('billing.changeCompletionStatusToActive');
	Route::post('update-bill-status/complete', [BillingController::class,'changeCompletionStatus'])->name('billing.changeCompletionStatus');
	Route::post('assign-bill-to-staff', [BillingController::class,'assignStaffToBooking'])->name('billing.assignStaffToBooking');
	Route::post('update-payment', [BillingController::class,'updatePayment'])->name('billing.update-payment');
	Route::post('cancel-booking', [BillingController::class,'cancelBooking'])->name('billing.cancelBooking');

    /* Reports Start */
    Route::get('reports/counter-wise',[ReportController::class,'counterWise'])->name('reports.counter-wise');
    Route::get('reports/daily-summary',[ReportController::class,'dailySummary'])->name('reports.daily-summary');
    Route::get('reports/daily-poojawise-summary',[ReportController::class,'dailyPoojawiseSummary'])->name('reports.daily-poojawise-summary');
    Route::get('reports/pooja-summary',[ReportController::class,'poojaSummary'])->name('reports.pooja-summary');
	Route::get('reports/pooja-summary/{vehicle_type}/detail',[ReportController::class,'poojaSummaryDetail'])->name('reports.pooja-summary-detail');
    Route::get('reports/bill-reprint',[ReportController::class,'billReprint'])->name('reports.bill-reprint');
    Route::get('reports/bill-details',[ReportController::class,'billDetailsbyId'])->name('reports.bill-details');
	Route::get('reports/pooja-list',[ReportController::class,'poojaList'])->name('reports.pooja-list');
	Route::get('reports/staff-wise',[ReportController::class,'staffWiseReport'])->name('reports.staff-wise');
	Route::get('reports/staff-wise/{user_id}/type-wise',[ReportController::class,'staffWiseTypeWiseReport'])->name('reports.staff-type-wise');
	Route::get('reports/staff-wise/{user_id}/type-wise/{vehicle_type_id}/detail',[ReportController::class,'staffWiseTypeWiseDetail'])->name('reports.staff-type-wise-detail');
	Route::get('reports/staff-wise-count',[ReportController::class,'staffWiseSummary'])->name('reports.staff-wise-count');
	Route::get('reports/staff-wise-count/{user_id}/type-wise',[ReportController::class,'staffWiseTypeWiseSummary'])->name('reports.staff-type-wise-count');
	Route::get('reports/staff-wise-count/{user_id}/type-wise/{vehicle_type_id}/detail',[ReportController::class,'staffWiseTypeWiseDetailSummary'])->name('reports.staff-type-wise-detail-count');
    /* Reports End */
    
    Route::post('/auth/logout', [AuthController::class,'logout']);
	Route::post('/delete-account', [AuthController::class, 'deleteAccount']);

});

	Route::post('signup', [CustomerController::class, 'signup']);
	Route::post('signin', [CustomerController::class, 'signin']);
	Route::post('verify-otp', [CustomerController::class, 'verifyOtp']);

	Route::get('franchises', [FranchiseController::class, 'getFranchises']);

	Route::post('book-single-wash', [BookingController::class, 'bookSingleWash']);
	Route::get('my-bookings', [BookingController::class, 'myBookings']);
	Route::get('/version', [VersionController::class, 'getVersion']);

Route::fallback(function () {
    // Return an "unauthenticated" response if the user is not authenticated
    return response()->json([
        'message' => 'Unauthenticated'
    ], 401);
});
