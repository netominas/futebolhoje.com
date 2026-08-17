<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

function url(string $path = ''): string
{
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function redirecionar(string $path, int $status = 302): never
{
    header('Location: ' . url($path), true, $status);
    exit;
}

function formatarDataHora(string $dataUtc): string
{
    $timestamp = strtotime($dataUtc);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : $dataUtc;
}

function formatarHora(string $dataUtc): string
{
    $timestamp = strtotime($dataUtc);
    return $timestamp ? date('H:i', $timestamp) : $dataUtc;
}

// Data por extenso em PT-BR (ex: "17 de agosto de 2026") sem depender da extensão intl
// nem do locale do servidor, que raramente vem configurado para pt_BR.
function formatarDataExtensa(?int $timestamp = null): string
{
    static $meses = [
        1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
        5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
        9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
    ];
    $timestamp ??= time();

    return (int) date('j', $timestamp) . ' de ' . $meses[(int) date('n', $timestamp)] . ' de ' . date('Y', $timestamp);
}

// Rótulo em PT-BR pros status curtos que a API-Football retorna (NS, 1H, HT, 2H, FT, ...)
function statusJogoLabel(string $statusShort, ?int $elapsed): string
{
    return match ($statusShort) {
        'NS' => 'Não iniciado',
        'TBD' => 'Horário a definir',
        '1H' => $elapsed !== null ? $elapsed . "'" : '1º tempo',
        'HT' => 'Intervalo',
        '2H' => $elapsed !== null ? $elapsed . "'" : '2º tempo',
        'ET' => 'Prorrogação',
        'BT' => 'Intervalo (prorrogação)',
        'P' => 'Pênaltis',
        'SUSP' => 'Suspenso',
        'INT' => 'Interrompido',
        'FT' => 'Encerrado',
        'AET' => 'Encerrado (prorrogação)',
        'PEN' => 'Encerrado (pênaltis)',
        'PST' => 'Adiado',
        'CANC' => 'Cancelado',
        'ABD' => 'Abandonado',
        'AWD' => 'W.O.',
        'WO' => 'W.O.',
        default => $statusShort,
    };
}

function jogoEstaAoVivo(string $statusShort): bool
{
    return in_array($statusShort, ['1H', 'HT', '2H', 'ET', 'BT', 'P'], true);
}

function jogoSlug(string $mandante, string $visitante): string
{
    return slugify($mandante . '-x-' . $visitante);
}
