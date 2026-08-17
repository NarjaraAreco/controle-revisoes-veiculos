<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SCHEMA = 'narjara_areco';

    /**
     * Cria o schema da desenvolvedora e move as tabelas do dominio para ele.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS "'.self::SCHEMA.'"');

        foreach (['people', 'vehicles', 'revisions'] as $table) {
            if ($this->tableExists('public', $table)) {
                DB::statement(sprintf(
                    'ALTER TABLE public."%s" SET SCHEMA "%s"',
                    $table,
                    self::SCHEMA,
                ));
            }
        }
    }

    /**
     * Retorna as tabelas do dominio para public ao desfazer a migration.
     */
    public function down(): void
    {
        foreach (['revisions', 'vehicles', 'people'] as $table) {
            if ($this->tableExists(self::SCHEMA, $table)) {
                DB::statement(sprintf(
                    'ALTER TABLE "%s"."%s" SET SCHEMA public',
                    self::SCHEMA,
                    $table,
                ));
            }
        }

        DB::statement('DROP SCHEMA IF EXISTS "'.self::SCHEMA.'"');
    }

    private function tableExists(string $schema, string $table): bool
    {
        $result = DB::selectOne(
            <<<'SQL'
                SELECT EXISTS (
                    SELECT 1
                    FROM pg_catalog.pg_tables
                    WHERE schemaname = ?
                      AND tablename = ?
                ) AS table_exists
            SQL,
            [$schema, $table],
        );

        return (bool) $result->table_exists;
    }
};
