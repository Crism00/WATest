<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncZohoContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-zoho-contacts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $zohoTokenService = new \App\Services\ZohoTokenService();
        $token = $zohoTokenService->getAccessToken();

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
        ])->get('https://www.zohoapis.com/crm/v2/Contacts');

        $contacts = $response->json('data');
        if($response->failed() || empty($contacts)) {
            $this->error('Error al obtener contactos de Zoho o no hay contactos disponibles.');
            return;
        }
        foreach ($contacts as $zohoContact) {
            \App\Models\Contacto::updateOrCreate(
                ['zoho_id' => $zohoContact['id']], // clave única
                [
                    'name' => $zohoContact['First_Name'] ?? 'Sin Nombre',
                    'phone' => $zohoContact['Phone'] ?? null,
                    'email' => $zohoContact['Email'] ?? null,
                    'course' => $zohoContact['Curso'] ?? null,
                    'last_name' => $zohoContact['Last_Name'],
                    'zoho_id' => $zohoContact['id'], // almacenar el ID de Zoho
                ]
            );
        }

        $this->info('Contactos sincronizados desde Zoho.');
    }
}
