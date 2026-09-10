<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\XenditService;
use Illuminate\Http\RedirectResponse;

/**
 * "Pay via GCash Now" flow, powered by Xendit's Payment Request API.
 *
 * This sits alongside the existing manual QR Ph + proof-upload flow in
 * User\BookingController@confirmPayment — nothing there was changed. The
 * booking is only ever marked 'confirmed' by XenditWebhookController once
 * Xendit itself confirms the payment succeeded (never from the browser
 * redirect back, which a customer could otherwise spoof by just visiting
 * the success URL).
 */
class XenditPaymentController extends Controller
{
    /**
     * Start a GCash payment for a booking and redirect to Xendit's hosted
     * checkout page.
     *
     * GET /user/bookings/{booking}/pay-gcash
     */
    public function pay(int $booking, XenditService $xendit): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        if (!in_array($bookingModel->status, ['pending_payment', 'awaiting_verification'])) {
            return redirect()
                ->route('user.bookings')
                ->with('error', 'This booking is no longer awaiting payment.');
        }

        try {
            $result = $xendit->createGcashPayment(
                $bookingModel,
                route('user.bookings.pay-gcash.success', $bookingModel->id),
                route('user.bookings.pay-gcash.failure', $bookingModel->id),
            );
        } catch (\Throwable $e) {
            return redirect()
                ->route('user.bookings.review', $bookingModel->id)
                ->with('error', 'Could not start GCash payment. Please try again or use the QR Ph option below.');
        }

        $bookingModel->update([
            'payment_method'             => 'gcash',
            'xendit_payment_request_id'  => $result['payment_request_id'],
            'xendit_reference_id'        => $result['reference_id'],
        ]);

        return redirect()->away($result['redirect_url']);
    }

    /**
     * Customer bounced back here after completing (or cancelling) payment
     * on Xendit's page. This is NOT what confirms the booking — that only
     * happens via the webhook — so this just shows a "we're checking"
     * message. By the time the customer lands here the webhook has often
     * already fired, but we don't rely on that.
     *
     * GET /user/bookings/{booking}/pay-gcash/success
     */
    public function success(int $booking): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        if ($bookingModel->status === 'confirmed') {
            return redirect()
                ->route('user.waiver')
                ->with('success', 'Payment confirmed! You can go ahead and sign the waiver.');
        }

        return redirect()
            ->route('user.bookings.review', $bookingModel->id)
            ->with('success', "We're confirming your GCash payment — this usually takes a few seconds. Refresh this page shortly, or check My Bookings.");
    }

    /**
     * Customer cancelled or failed the GCash payment on Xendit's page.
     *
     * GET /user/bookings/{booking}/pay-gcash/failure
     */
    public function failure(int $booking): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        return redirect()
            ->route('user.bookings.review', $bookingModel->id)
            ->with('error', 'GCash payment was not completed. You can try again or use the QR Ph option below.');
    }
}
