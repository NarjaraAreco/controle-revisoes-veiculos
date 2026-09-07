<?php

namespace App\Concerns;

use Closure;
use Illuminate\Support\Facades\Cache;

final class DataCache
{
    private const VERSION_KEY = 'application-data-cache-version';

    /**
     * Mantém dados de leitura por um dia como proteção contra alterações feitas
     * fora da aplicação. A invalidação normal acontece quando há uma mutação.
     */
    public static function remember(string $key, Closure $callback): mixed
    {
        $version = (int) Cache::get(self::VERSION_KEY, 1);
        $cacheKey = "application-data:v{$version}:{$key}";
        $cached = Cache::get($cacheKey);

        // Remove entradas gravadas por versões anteriores que não conseguem
        // ser desserializadas depois que o código muda.
        if (is_object($cached) && get_class($cached) === '__PHP_Incomplete_Class') {
            Cache::forget($cacheKey);
        } elseif ($cached !== null) {
            return $cached;
        }

        return Cache::remember(
            $cacheKey,
            now()->addDay(),
            $callback,
        );
    }

    /**
     * Cria uma nova versão e torna todos os dados de leitura anteriores obsoletos.
     */
    public static function invalidate(): void
    {
        $version = (int) Cache::get(self::VERSION_KEY, 1);

        Cache::forever(self::VERSION_KEY, $version + 1);
    }
}
