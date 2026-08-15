<?php

declare(strict_types=1);

final class ApiFootball
{
    public static function leagues(): array
    {
        return self::get('/leagues');
    }

    public static function fixturesByDate(string $dataYmd): array
    {
        return self::get('/fixtures', ['date' => $dataYmd]);
    }

    public static function fixturesLive(): array
    {
        return self::get('/fixtures', ['live' => 'all']);
    }

    public static function fixtureEvents(int $fixtureId): array
    {
        return self::get('/fixtures/events', ['fixture' => $fixtureId]);
    }

    public static function fixtureStatistics(int $fixtureId): array
    {
        return self::get('/fixtures/statistics', ['fixture' => $fixtureId]);
    }

    public static function standings(int $leagueId, int $temporada): array
    {
        return self::get('/standings', ['league' => $leagueId, 'season' => $temporada]);
    }

    /** @return array<int, array<string, mixed>> */
    private static function get(string $endpoint, array $query = []): array
    {
        if (API_FOOTBALL_KEY === '') {
            throw new RuntimeException('API_FOOTBALL_KEY não configurada (defina FUTEBOLHOJE_API_FOOTBALL_KEY).');
        }

        $viaRapidApi = str_ends_with(API_FOOTBALL_HOST, 'rapidapi.com');
        // O host direto (v3.football.api-sports.io) já embute a versão no próprio domínio;
        // via RapidAPI o domínio é genérico e a versão entra no caminho da URL.
        $basePath = $viaRapidApi ? '/v3' : '';

        $url = 'https://' . API_FOOTBALL_HOST . $basePath . $endpoint;
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        $headers = $viaRapidApi
            ? [
                'x-rapidapi-key: ' . API_FOOTBALL_KEY,
                'x-rapidapi-host: ' . API_FOOTBALL_HOST,
            ]
            : [
                'x-apisports-key: ' . API_FOOTBALL_KEY,
            ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 20,
        ]);

        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erro = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new RuntimeException("Falha ao chamar API-Football ({$endpoint}): {$erro}");
        }

        if ($status !== 200) {
            throw new RuntimeException("API-Football retornou HTTP {$status} em {$endpoint}: " . substr($body, 0, 300));
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded) || !isset($decoded['response'])) {
            throw new RuntimeException("Resposta inesperada da API-Football em {$endpoint}");
        }

        if (!empty($decoded['errors'])) {
            throw new RuntimeException("API-Football retornou erro em {$endpoint}: " . json_encode($decoded['errors'], JSON_UNESCAPED_UNICODE));
        }

        return $decoded['response'];
    }
}
