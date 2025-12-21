<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WhatsappContact;
use App\Models\WhatsappBusinessProfile;

class WhatsappContactSeeder extends Seeder
{
    public function run()
    {
        // Asegurarnos de que existe un perfil de negocio
        $businessProfile = WhatsappBusinessProfile::first();

        if (!$businessProfile) {
            $businessProfile = WhatsappBusinessProfile::create([
                'whatsapp_business_id' => '123456789',
                'phone_number' => '593967720288',
                'business_name' => 'Herbalife Marketing',
                'display_name' => 'Herbalife Marketing',
                'status' => 'active'
            ]);
        }

        // Crear contactos de prueba
        $contacts = [
            [
                'phone_number' => '593988492339',
                'name' => 'Gregorio Osorio',
                'status' => 'active',
                'business_profile_id' => $businessProfile->id
            ]


        ];

        foreach ($contacts as $contact) {
            WhatsappContact::updateOrCreate(
                ['phone_number' => $contact['phone_number']],
                $contact
            );
        }
    }
}
