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
                Pembayaran Invoice
            </p>
        </div>

        <!-- Invoice Card -->
        <div class="glass rounded-2xl shadow-xl p-6 sm:p-8 mb-6">
            <!-- Invoice Status -->
            <div class="mb-6">
                @if($invoice->status === 'paid')
                    <div class="flex items-center justify-center gap-2 text-success-600 dark:text-success-400 bg-success-50 dark:bg-success-900/20 px-4 py-3 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold">Invoice Lunas</span>
                    </div>
                @elseif($invoice->status === 'cancelled')
                    <div class="flex items-center justify-center gap-2 text-danger-600 dark:text-danger-400 bg-danger-50 dark:bg-danger-900/20 px-4 py-3 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold">Invoice Dibatalkan</span>
                    </div>
                @else
                    <div class="flex items-center justify-center gap-2 text-warning-600 dark:text-warning-400 bg-warning-50 dark:bg-warning-900/20 px-4 py-3 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold">Menunggu Pembayaran</span>
                    </div>
                @endif
            </div>

            <!-- Invoice Details -->
            <div class="space-y-4 mb-6">
                <div class="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">Nomor Invoice</span>
                    <span class="font-mono font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</span>
                </div>

                <div class="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">Nama Pelanggan</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $invoice->booking->name }}</span>
                </div>

                <div class="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">Kendaraan</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $invoice->booking->car_model }}</span>
                </div>

                <div class="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">Layanan</span>
                    <span class="font-semibold text-gray-900 dark:text-white capitalize">{{ str_replace('-', ' ', $invoice->booking->service_type) }}</span>
                </div>

                <div class="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">Status</span>
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        @if($invoice->status === 'paid') bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400
                        @elseif($invoice->status === 'cancelled') bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400
                        @else bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400
                        @endif">
                        {{ $invoice->status_label }}
                    </span>
                </div>
            </div>

            <!-- Invoice Items -->
            @if($invoice->items->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Detail Item</h3>
                    <div class="space-y-2">
                        @foreach($invoice->items as $item)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ $item->name }} x {{ $item->qty }} {{ $item->unit ?? '' }}
                                </span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Totals -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                    <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Pajak ({{ $invoice->tax_percent }}%)</span>
                        <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($invoice->discount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Diskon</span>
                        <span class="font-medium text-success-600 dark:text-success-400">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center pt-3 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Total</span>
                    <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        Rp {{ number_format($invoice->total, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Payment Button -->
        @if($invoice->isPending())
            <div class="glass rounded-2xl shadow-xl p-6 sm:p-8">
                <div id="payment-button-container">
                    @if($invoice->payment_url)
                        <a href="{{ $invoice->payment_url }}" target="_blank"
                           class="block w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-4 px-6 rounded-xl text-center transition-colors duration-200">
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Bayar Sekarang
                            </div>
                        </a>
                    @else
                        <button onclick="generatePaymentLink()"
                                class="block w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-4 px-6 rounded-xl text-center transition-colors duration-200">
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Buat Link Pembayaran
                            </div>
                        </button>
                    @endif
                </div>

                <div id="loading" class="hidden">
                    <div class="flex items-center justify-center gap-2 text-gray-600 dark:text-gray-400">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memproses pembayaran...</span>
                    </div>
                </div>

                <div id="error-message" class="hidden mt-4 p-4 bg-danger-50 dark:bg-danger-900/20 rounded-xl">
                    <p class="text-danger-600 dark:text-danger-400 text-sm"></p>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-4">
                    Pembayaran diproses melalui Midtrans. Anda akan diarahkan ke halaman pembayaran yang aman.
                </p>
            </div>
        @elseif($invoice->isPaid())
            <div class="glass rounded-2xl shadow-xl p-6 sm:p-8 text-center">
                <div class="w-16 h-16 bg-success-100 dark:bg-success-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    Pembayaran Berhasil
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Terima kasih! Pembayaran Anda telah kami terima.
                </p>
                <a href="{{ route('invoice.print', $invoice->invoice_number) }}"
                   class="inline-flex items-center gap-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-medium py-3 px-6 rounded-xl hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Unduh Invoice
                </a>
            </div>
        @endif

        <!-- Footer -->
        <div class="text-center mt-8 text-sm text-gray-500 dark:text-gray-400">
            <p>Butuh bantuan? Hubungi kami di</p>
            <p class="font-medium">+62 812 3456 7890</p>
        </div>
    </div>
</div>

<script>
async function generatePaymentLink() {
    const invoiceNumber = '{{ $invoice->invoice_number }}';
    const buttonContainer = document.getElementById('payment-button-container');
    const loading = document.getElementById('loading');
    const errorMessage = document.getElementById('error-message');

    loading.classList.remove('hidden');
    buttonContainer.classList.add('hidden');
    errorMessage.classList.add('hidden');

    try {
        const response = await fetch(`/api/payment/${invoiceNumber}/generate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (data.success && data.payment_url) {
            window.open(data.payment_url, '_blank');
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            throw new Error(data.message || 'Gagal membuat link pembayaran');
        }
    } catch (error) {
        errorMessage.querySelector('p').textContent = error.message;
        errorMessage.classList.remove('hidden');
        buttonContainer.classList.remove('hidden');
    } finally {
        loading.classList.add('hidden');
    }
}

// Auto-refresh status every 10 seconds
@if($invoice->isPending())
setInterval(() => {
    fetch(`/api/payment/{{ $invoice->invoice_number }}/status`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.invoice.is_paid) {
                window.location.reload();
            }
        });
}, 10000);
@endif
</script>
@endsection
