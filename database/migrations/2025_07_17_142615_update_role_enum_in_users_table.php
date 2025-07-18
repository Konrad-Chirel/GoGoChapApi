<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Étape 1 : Supprimer la contrainte existante
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");

        // Étape 2 : Corriger les rôles invalides déjà présents
        DB::table('users')
            ->where('role', 'livreur_entreprise')
            ->update(['role' => 'entreprise_livraison']);

        // Étape 3 : Ajouter la nouvelle contrainte
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (
            role IN ('client', 'partenaire', 'livreur', 'entreprise_livraison', 'admin')
        )");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");

        // Remettre les rôles comme avant si besoin
        DB::table('users')
            ->where('role', 'entreprise_livraison')
            ->update(['role' => 'livreur_entreprise']);

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (
            role IN ('client', 'partenaire', 'livreur', 'livreur_entreprise', 'admin')
        )");
    }
};
