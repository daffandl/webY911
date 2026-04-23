<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\FonnteService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Mark as Sent
            Actions\Action::make('mark_sent')
                ->label('Tandai Terkirim')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->visible(fn () => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'sent']);
                    Notification::make()->title('📤 Invoice ditandai terkirim.')->info()->send();
                    $this->refreshFormData(['status']);
                }),

            // Mark as Paid
            Actions\Action::make('mark_paid')
                ->label('Tandai Lunas')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['draft', 'sent']))
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'paid']);
                    Notification::make()->title('✅ Invoice ditandai lunas.')->success()->send();
                    $this->refreshFormData(['status']);
                }),

            // Send Invoice to Customer
            Actions\Action::make('send_invoice')
                ->label('Kirim ke Customer')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->visible(fn () => in_array($this->record->status, ['draft', 'sent', 'paid']))
                ->requiresConfirmation()
                ->modalHeading('Kirim Invoice ke Customer')
                ->modalDescription('Invoice akan dikirim via WhatsApp dan Email ke customer.')
                ->action(function () {
                    $invoice = $this->record->load(['booking', 'items']);

                    // Send WA
                    try {
                        app(FonnteService::class)->notifyUserInvoice($invoice);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('Invoice WA error: ' . $e->getMessage());
                    }

                    // Send Email
                    try {
                        if ($invoice->booking->email) {
                            \Illuminate\Support\Facades\Mail::to($invoice->booking->email)
                                ->send(new \App\Mail\UserInvoiceMail($invoice));
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('Invoice Email error: ' . $e->getMessage());
                    }

                    // Update status to sent if still draft
                    if ($invoice->status === 'draft') {
                        $invoice->update(['status' => 'sent']);
                        $this->refreshFormData(['status']);
                    }

                    Notification::make()
                        ->title('📤 Invoice berhasil dikirim!')
                        ->body("WA & Email telah dikirim ke {$invoice->booking->name}.")
                        ->success()
                        ->send();
                }),

            // Open Print View
            Actions\Action::make('print_invoice')
                ->label('Lihat Invoice')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn (): string => url('/invoice/' . $this->record->invoice_number . '/print'))
                ->openUrlInNewTab(),

            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
