@extends('payment.layout')

@section('content')
<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                Young 911 Autowerks
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Status Pembayaran
            </p>
        </div>

        <!-- Status Card -->
        <div class="glass rounded-2xl shadow-xl p-6 sm:p-8">
            @if($status === 'success' || $status === 'settlement')
                <div class="text-center">
                    <div class="w-20 h-20 bg-success-100 dark:bg-success-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        Pembayaran Berhasil!
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Terima kasih! Pembayaran invoice Anda telah berhasil diproses.
                    </p>
                </div>
            @elseif($status === 'pending')
                <div class="text-center">
                    <div class="w-20 h-20 bg-warning-100 dark:bg-warning-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        Menunggu Pembayaran
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Pembayaran Anda sedang diproses. Silakan selesaikan pembayaran.
                    </p>
                </div>
            @elseif($status === 'failure' || $status === 'deny' || $status === 'expire' || $status === 'cancel')
                <div class="text-center">
                    <div class="w-20 h-20 bg-danger-100 dark:bg-danger-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-danger-600 dark:text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        Pembayaran Gagal
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Pembayaran Anda tidak dapat diproses. Silakan coba lagi.
                    </p>
                </div>
            @else
                <div class="text-center">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        Status Pembayaran
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Silakan cek status pembayaran Anda.
                    </p>
                </div>
            @endif

            <!-- Invoice Info -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600 dark:text-gray-400">Invoice</span>
                    <span class="font-mono font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Total</span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($invoice->total, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                @if($invoice->isPaid())
                    <a href="{{ route('invoice.print', $invoice->invoice_number) }}"
                       class="block w-full bg-success-600 hover:bg-success-700 text-white font-semibold py-3 px-6 rounded-xl text-center transition-colors">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Unduh Invoice
                        </div>
                    </a>
                @elseif(!$invoice->isCancelled())
                    <a href="{{ route('payment.show', $invoice->invoice_number) }}"
                       class="block w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-6 rounded-xl text-center transition-colors">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Lanjutkan Pembayaran
                        </div>
                    </a>
                @endif

                <a href="{{ route('invoice.print', $invoice->invoice_number) }}"
                   class="block w-full bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-900 dark:text-white font-semibold py-3 px-6 rounded-xl text-center transition-colors">
                    Lihat Detail Invoice
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-sm text-gray-500 dark:text-gray-400">
            <p>Butuh bantuan? Hubungi kami di</p>
            <p class="font-medium">+62 812 3456 7890</p>
        </div>
    </div>
</div>

<script>
// Auto-refresh for pending payments
@if($status === 'pending')
setTimeout(() => {
    window.location.href = '{{ route('payment.show', $invoice->invoice_number) }}';
}, 5000);
@endif
</script>
@endsection
