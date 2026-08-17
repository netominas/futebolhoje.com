<?php

declare(strict_types=1);

// Cliente mínimo para a Messages API da Anthropic via curl (sem SDK, consistente
// com o resto do projeto — ver core/ApiFootball.php para o mesmo padrão).
final class ClaudeApi
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';
    private const VERSAO_API = '2023-06-01';

    public static function gerarTexto(string $prompt, int $maxTokens = 1024, ?string $sistema = null): string
    {
        if (ANTHROPIC_API_KEY === '') {
            throw new RuntimeException('ANTHROPIC_API_KEY não configurada (defina FUTEBOLHOJE_ANTHROPIC_API_KEY).');
        }

        $payload = [
            'model' => ANTHROPIC_MODEL,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];
        if ($sistema !== null) {
            $payload['system'] = $sistema;
        }

        $corpo = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $ch = curl_init(self::ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $corpo,
            CURLOPT_HTTPHEADER => [
                'content-type: application/json',
                'x-api-key: ' . ANTHROPIC_API_KEY,
                'anthropic-version: ' . self::VERSAO_API,
            ],
            CURLOPT_TIMEOUT => 60,
        ]);

        $resposta = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erro = curl_error($ch);
        curl_close($ch);

        if ($resposta === false) {
            throw new RuntimeException("Falha ao chamar a API da Anthropic: {$erro}");
        }

        if ($status !== 200) {
            throw new RuntimeException("API da Anthropic retornou HTTP {$status}: " . substr($resposta, 0, 300));
        }

        $decodificado = json_decode($resposta, true);
        if (!is_array($decodificado) || !isset($decodificado['content'][0]['text'])) {
            throw new RuntimeException('Resposta inesperada da API da Anthropic: ' . substr($resposta, 0, 300));
        }

        return trim((string) $decodificado['content'][0]['text']);
    }
}
