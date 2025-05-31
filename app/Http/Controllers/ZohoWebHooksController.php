<?php

namespace App\Http\Controllers;

use App\Models\contacto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZohoWebHooksController extends Controller
{
    public function newContact(Request $request)
    {
        $token = app('App\Services\ZohoTokenService')->getAccessToken();
        $zohoContactData = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
        ])->get('https://www.zohoapis.com/crm/v8/Contacts/' . $request->input('id'));
        if ($zohoContactData->failed()) {
            Log::error('Failed to fetch contact from Zoho', [
                'status' => $zohoContactData->status(),
                'response' => $zohoContactData->json(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch contact from Zoho'], 500);
        }
        $contactData = $zohoContactData->json()['data'][0];
        $contact = new \App\Models\Contacto();
        $contact->name = $contactData['First_Name'] ?? null;
        $contact->phone = $contactData['Phone'] ?? null;
        $contact->email = $contactData['Email'] ?? null;
        $contact->course = $contactData['Curso'] ?? null;
        $contact->last_name = $contactData['Last_Name'] ?? null;
        $contact->zoho_id = $contactData['id'] ?? null;
        $contact->save();
        Log::info('New contact created from Zoho webhook', [
            'contact_id' => $contact->id,
            'zoho_id' => $contact->zoho_id,
        ]);

        return response()->json(['status' => 'success'], 200);
    }

    public function editedContact(Request $request)
    {
        log::info('Received Zoho contact edit webhook', [
            'contact_id' => $request->input('id'),
        ]);
        $token = app('App\Services\ZohoTokenService')->getAccessToken();
        $zohoContactData = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
        ])->get('https://www.zohoapis.com/crm/v8/Contacts/' . $request->input('id'));
        if ($zohoContactData->failed()) {
            Log::error('Failed to fetch contact from Zoho', [
                'status' => $zohoContactData->status(),
                'response' => $zohoContactData->json(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch contact from Zoho'], 500);
        }
        $contactData = $zohoContactData->json()['data'][0];
        $contact = \App\Models\Contacto::where('zoho_id', $contactData['id'])->first();
        if (!$contact) {
            Log::warning('Contact not found in local database', [
                'zoho_id' => $contactData['id'],
            ]);
            return response()->json(['status' => 'error', 'message' => 'Contact not found'], 404);
        }
        $contact->name = $contactData['First_Name'] ?? $contact->name;
        $contact->phone = $contactData['Phone'] ?? $contact->phone;
        $contact->email = $contactData['Email'] ?? $contact->email;
        $contact->course = $contactData['Curso'] ?? $contact->course;
        $contact->last_name = $contactData['Last_Name'] ?? $contact->last_name;
        $contact->save();
        Log::info('Contact updated from Zoho webhook', [
            'contact_id' => $contact->id,
            'zoho_id' => $contact->zoho_id,
        ]);
        return response()->json(['status' => 'success'], 200);
    }

    public function leadToContact(Request $request)
    {
        Log::info('Converting lead to contact', [
            $request->all(),
        ]);
        $contact = new contacto();
        $contact->name = $request->input('name');
        $contact->phone = $request->input('phone');
        $contact->email = $request->input('email');
        $contact->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Lead converted to contact successfully',
            'data' => $contact,
        ], 201);
    }

}
