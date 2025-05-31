<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ZohoTokenService
{
    protected $clientId;
    protected $clientSecret;
    protected $refreshToken;
    protected $accountsUrl;

    public function __construct()
    {
        $this->clientId = config('services.zoho.client_id');
        $this->clientSecret = config('services.zoho.client_secret');
        $this->refreshToken = config('services.zoho.refresh_token');
        $this->accountsUrl = config('services.zoho.accounts_url');
    }

    public function getAccessToken()
    {
        // Verifica si hay token y si aún es válido
        if (Cache::has('zoho_access_token')) {
            return Cache::get('zoho_access_token');
        }

        return $this->refreshAccessToken();
    }

    public function refreshAccessToken()
    {
        $response = Http::asForm()->post("{$this->accountsUrl}/oauth/v2/token", [
            'refresh_token' => $this->refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            throw new \Exception('Error al refrescar el access token de Zoho');
        }

        $data = $response->json();

        $accessToken = $data['access_token'];
        $expiresIn = $data['expires_in'] ?? 3600; // segundos

        Cache::put('zoho_access_token', $accessToken, now()->addSeconds($expiresIn - 60)); // margen de 1 min

        return $accessToken;
    }
}
