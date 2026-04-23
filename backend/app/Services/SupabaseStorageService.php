<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SupabaseStorageService
{
    protected string $url;
    protected string $key;
    protected string $bucket;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->key = config('services.supabase.key');
        $this->bucket = 'profile-photos';
    }

    /**
     * Upload file to Supabase Storage
     */
    public function upload(UploadedFile $file, ?string $bucket = null): string|false
    {
        try {
            $bucket = $bucket ?? $this->bucket;
            
            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = Str::random(40) . ($extension ? '.' . $extension : '');

            // Read file content as binary
            $fileContent = file_get_contents($file->getRealPath());
            
            if ($fileContent === false) {
                Log::error('Failed to read file content');
                return false;
            }

            // Supabase Storage API URL (note: "object" not "objects")
            $uploadUrl = rtrim($this->url, '/') . "/storage/v1/object/{$bucket}/{$filename}";

            // Make HTTP PUT request using file content directly
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->key}",
                'apikey' => $this->key,
                'Content-Type' => $file->getMimeType(),
                'x-upsert' => 'false',
            ])->withBody($fileContent, $file->getMimeType())->put($uploadUrl);

            if ($response->successful()) {
                // Return public URL
                return $this->getPublicUrl($bucket, $filename);
            }

            Log::error('Supabase upload failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'filename' => $filename,
            ]);

            return false;
            
        } catch (\Exception $e) {
            Log::error('Supabase upload exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Get public URL for a file
     */
    public function getPublicUrl(string $bucket, string $filename): string
    {
        // Public URL uses "object" (singular)
        return rtrim($this->url, '/') . "/storage/v1/object/public/{$bucket}/{$filename}";
    }

    /**
     * Delete file from Supabase Storage
     */
    public function delete(string $filename, ?string $bucket = null): bool
    {
        try {
            $bucket = $bucket ?? $this->bucket;
            
            $deleteUrl = rtrim($this->url, '/') . "/storage/v1/objects/{$bucket}/{$filename}";

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->key}",
                'apikey' => $this->key,
            ])->delete($deleteUrl);

            return $response->successful();
            
        } catch (\Exception $e) {
            Log::error('Supabase delete exception', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Extract filename from URL
     */
    public function extractFilename(string $url): ?string
    {
        // URL format: https://xxx.supabase.co/storage/v1/objects/public/bucket/filename
        $parts = explode('/storage/v1/objects/public/', $url);
        if (count($parts) < 2) {
            return null;
        }
        
        $pathParts = explode('/', $parts[1]);
        return end($pathParts);
    }

    /**
     * Extract bucket from URL
     */
    public function extractBucket(string $url): ?string
    {
        $parts = explode('/storage/v1/objects/public/', $url);
        if (count($parts) < 2) {
            return null;
        }
        
        $pathParts = explode('/', $parts[1]);
        if (count($pathParts) < 2) {
            return null;
        }
        
        return $pathParts[0];
    }
}
