<?php

namespace App\Http\Controllers;

use App\Services\ZohoTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ContactsController extends Controller
{
    public function index()
    {
        return Inertia::render('Contacts/Index', [
            'contacts' => \App\Models\Contacto::all(),
        ]);
    }
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:contactos,phone',
            'email' => 'nullable|email|max:255',
            'course' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $contact = new \App\Models\Contacto();
        $contact->name = $request->input('name');
        $contact->phone = $request->input('phone');
        $contact->email = $request->input('email');
        $contact->course = $request->input('course');
        $contact->last_name = $request->input('last_name');
        $zohoTokenService = new ZohoTokenService();
        $token = $zohoTokenService->getAccessToken();

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
        ])->post('https://www.zohoapis.com/crm/v2/Contacts', [
            'data' => [
                [
                    'First_Name' => $contact->name,
                    'Phone' => $contact->phone,
                    'Email' => $contact->email,
                    'Curso' => $contact->course, // Asegúrate de que este campo exista en Zoho
                    'Last_Name' => $contact->last_name,
                ]
            ]
        ]);
        if ($response->failed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create contact in Zoho',
                'details' => $response->json(),
            ], 500);
        }
        $zohoData = $response->json();
        if (isset($zohoData['data'][0]['code']) && $zohoData['data'][0]['code'] === 'DUPLICATE_DATA' && $zohoData['data'][0]['details']['api_name'] === 'Phone') {
            // Si hay un error de datos duplicados, significa que el número de teléfono ya existe
            $contact->zoho_id = $zohoData['data'][0]['details']['id'] ?? null;
            if (!$contact->zoho_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to retrieve Zoho ID for the contact',
                ], 500);
            }
            $zohoContact = Http::withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $token,
            ])->get("https://www.zohoapis.com/crm/v2/Contacts/{$contact->zoho_id}");
            $zohoContactData = $zohoContact->json();
            $contact->name = $zohoContactData['data'][0]['First_Name'] ?? $contact->name;
            $contact->phone = $zohoContactData['data'][0]['Phone'] ?? $contact->phone;
            $contact->email = $zohoContactData['data'][0]['Email'] ?? $contact->email;
            $contact->course = $zohoContactData['data'][0]['Curso'] ?? $contact->course;
            $contact->last_name = $zohoContactData['data'][0]['Last_Name'] ?? $contact->last_name;
            $contact->zoho_id = $zohoData['data'][0]['details']['id'] ?? null;
            $contact->save();
            return response()->json([
                'status' => 'error',
                'message' => 'Duplicate data: the phone number already exists in Zoho. Existing Contact Syncronized.',
                'details' => $zohoData['data'][0]['details'] ?? [],
            ], 409);
        }
        if (isset($zohoData['data'][0]['details']['id'])) {
            $contact->zoho_id = $zohoData['data'][0]['details']['id'];
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve Zoho ID for the contact',
            ], 500);
        }
        if($contact->save()){
            return response()->json([
                'status' => 'success',
                'message' => 'Contact created successfully',
                'data' => $contact,
            ], 201);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save contact locally',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $contact = \App\Models\Contacto::find($id);
        if (!$contact) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contact not found',
            ], 404);
        }

        $token = app(ZohoTokenService::class)->getAccessToken();
        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
        ])->get("https://www.zohoapis.com/crm/v2/Contacts/".$contact->zoho_id);
        if ($response->failed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch contact from Zoho',
                'details' => $response->json(),
            ], 500);
        }
        $zohoContactData = $response->json();
        if (empty($zohoContactData['data'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contact not found in Zoho',
            ], 404);
        }
        $zohoContact = $zohoContactData['data'][0];
        $id = $zohoContact['id'] ?? $id;
        if (!$id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contact ID is required',
            ], 400);
        }
        $dbId = $contact->id;
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:15|unique:contactos,phone,' . $dbId. ',id',
            'email' => 'nullable|email|max:255',
            'course' => 'nullable|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
        ])->put("https://www.zohoapis.com/crm/v2/Contacts/{$id}", [
            'data' => [
                [
                    'id' => $id,
                    'First_Name' => $request->input('name', $zohoContact['First_Name'] ?? null),
                    'Phone' => $request->input('phone', $zohoContact['Phone'] ?? null),
                    'Email' => $request->input('email', $zohoContact['Email'] ?? null),
                    'Curso' => $request->input('course', $zohoContact['Curso'] ?? null),
                    'Last_Name' => $request->input('last_name', $zohoContact['Last_Name']),
                ]
            ]
        ]);
        if ($response->failed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update contact in Zoho',
                'details' => $response->json(),
            ], 500);
        }
        $contact->fill($request->only(['name', 'phone', 'email', 'course']));
        if ($contact->save()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Contact updated successfully',
                'data' => $contact,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update contact',
            ], 500);
        }
    }
}
