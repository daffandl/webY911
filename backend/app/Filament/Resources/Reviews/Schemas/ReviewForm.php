<?php

namespace App\Filament\Resources\Reviews\Schemas;

use App\Services\SupabaseStorageService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_name')
                    ->label('Nama Pengguna')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('profile_photo')
                    ->label('Foto Profil')
                    ->image()
                    ->disk('supabase')
                    ->directory('profile-photos')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->nullable()
                    ->saveUploadedFileUsing(function (FileUpload $component, \Illuminate\Http\UploadedFile $file) {
                        $supabaseUrl = config('services.supabase.url');
                        $supabaseKey = config('services.supabase.key');
                        
                        // Generate unique filename
                        $extension = $file->getClientOriginalExtension();
                        $filename = Str::random(40) . ($extension ? '.' . $extension : '');
                        
                        // Read file content
                        $fileContent = file_get_contents($file->getRealPath());
                        
                        // Supabase Storage API URL (note: "object" not "objects")
                        $bucket = 'profile-photos';
                        $uploadUrl = rtrim($supabaseUrl, '/') . "/storage/v1/object/{$bucket}/{$filename}";
                        
                        // Make HTTP PUT request
                        $response = \Illuminate\Support\Facades\Http::withHeaders([
                            'Authorization' => "Bearer {$supabaseKey}",
                            'apikey' => $supabaseKey,
                            'Content-Type' => $file->getMimeType(),
                            'x-upsert' => 'false',
                        ])->withBody($fileContent, $file->getMimeType())->put($uploadUrl);
                        
                        if ($response->successful()) {
                            // Return public URL
                            return rtrim($supabaseUrl, '/') . "/storage/v1/object/public/{$bucket}/{$filename}";
                        }
                        
                        return null;
                    })
                    ->deleteUploadedFileUsing(function (FileUpload $component, string $fileUrl) {
                        $supabaseUrl = config('services.supabase.url');
                        $supabaseKey = config('services.supabase.key');
                        
                        // Extract filename from URL
                        $parts = explode('/storage/v1/object/public/profile-photos/', $fileUrl);
                        if (count($parts) < 2) {
                            return false;
                        }
                        $filename = $parts[1];
                        
                        // Delete from Supabase
                        $deleteUrl = rtrim($supabaseUrl, '/') . "/storage/v1/object/profile-photos/{$filename}";
                        
                        $response = \Illuminate\Support\Facades\Http::withHeaders([
                            'Authorization' => "Bearer {$supabaseKey}",
                            'apikey' => $supabaseKey,
                        ])->delete($deleteUrl);
                        
                        return $response->successful();
                    }),

                TextInput::make('vehicle_info')
                    ->label('Info Kendaraan')
                    ->maxLength(255)
                    ->nullable(),

                Select::make('rating')
                    ->label('Rating')
                    ->options([
                        1 => '1 - Sangat Buruk',
                        2 => '2 - Buruk',
                        3 => '3 - Cukup',
                        4 => '4 - Baik',
                        5 => '5 - Sangat Baik',
                    ])
                    ->required()
                    ->native(false),

                Textarea::make('comment')
                    ->label('Komentar')
                    ->required()
                    ->rows(4)
                    ->maxLength(1000),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending')
                    ->required()
                    ->native(false),
            ]);
    }
}
