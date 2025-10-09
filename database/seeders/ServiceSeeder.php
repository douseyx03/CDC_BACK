<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@cdc.local'],
            [
                'nom' => 'Admin',
                'prenom' => 'CDC',
                'telephone' => '+221700000000',
                'password' => Hash::make('password123'),
            ]
        );

        Institution::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'nom_institution' => 'CDC',
                'type_institution' => 'institution_gouvernementale',
            ]
        );

        $services = [
            [
                'nom' => 'Assistance administrative',
                'description' => 'Accompagnement complet pour vos démarches administratives.',
                'delai' => '5 jours ouvrés',
                'montant_min' => 5000,
                'avantage' => [
                    'Gain de temps garanti',
                    'Suivi personnalisé',
                    'Experts dédiés',
                ],
                'document_requis' => [
                    'Pièce d’identité',
                    'Justificatif de domicile',
                    'Formulaire signé',
                ],
            ],
            [
                'nom' => 'Conseil juridique express',
                'description' => 'Consultation juridique pour particuliers et entreprises.',
                'delai' => '72 heures',
                'montant_min' => 15000,
                'avantage' => [
                    'Avis d’experts',
                    'Confidentialité assurée',
                    'Support multicanal',
                ],
                'document_requis' => [
                    'Pièce d’identité',
                    'Résumé de la situation',
                    'Documents justificatifs',
                ],
            ],
            [
                'nom' => 'Création de dossier d’investissement',
                'description' => 'Montage et vérification complète de votre dossier.',
                'delai' => '10 jours',
                'montant_min' => 30000,
                'avantage' => [
                    'Analyse de faisabilité',
                    'Dossier conforme',
                    'Suivi des échéances',
                ],
                'document_requis' => [
                    'Business plan',
                    'Pièces d’identité des dirigeants',
                    'Justificatifs financiers',
                ],
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['nom' => $service['nom']],
                array_merge($service, ['user_id' => $admin->id])
            );
        }
    }
}
