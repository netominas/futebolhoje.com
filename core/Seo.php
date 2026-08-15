<?php

declare(strict_types=1);

final class Seo
{
    private static string $title = '';
    private static string $description = '';
    private static string $canonical = '';
    private static string $ogImage = '';
    /** @var array<int, array<string, mixed>> */
    private static array $jsonLd = [];
    /** @var array<int, array{nome: string, url: string}> */
    private static array $breadcrumbItems = [];

    public static function set(string $title, string $description, ?string $canonicalPath = null, ?string $ogImage = null): void
    {
        self::$title = $title;
        self::$description = $description;
        self::$canonical = $canonicalPath !== null ? url($canonicalPath) : url($_SERVER['REQUEST_URI'] ?? '/');
        self::$ogImage = $ogImage ?? asset('img/og-default.svg');
    }

    public static function addJsonLd(array $data): void
    {
        self::$jsonLd[] = $data;
    }

    public static function breadcrumbs(array $itens): void
    {
        self::$breadcrumbItems = $itens;
        $list = [];
        foreach ($itens as $posicao => $item) {
            $list[] = [
                '@type' => 'ListItem',
                'position' => $posicao + 1,
                'name' => $item['nome'],
                'item' => url($item['url']),
            ];
        }

        self::addJsonLd([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ]);
    }

    public static function title(): string
    {
        return self::$title !== '' ? self::$title . ' | ' . SITE_NAME : SITE_NAME;
    }

    public static function description(): string
    {
        return self::$description !== '' ? self::$description : SITE_DESCRIPTION;
    }

    public static function canonical(): string
    {
        return self::$canonical !== '' ? self::$canonical : url('/');
    }

    public static function ogImage(): string
    {
        return self::$ogImage !== '' ? self::$ogImage : asset('img/og-default.svg');
    }

    public static function renderHead(): string
    {
        $html = '';
        $html .= '<title>' . e(self::title()) . "</title>\n";
        $html .= '<meta name="description" content="' . e(self::description()) . "\">\n";
        $html .= '<link rel="canonical" href="' . e(self::canonical()) . "\">\n";

        $html .= '<meta property="og:type" content="website">' . "\n";
        $html .= '<meta property="og:site_name" content="' . e(SITE_NAME) . "\">\n";
        $html .= '<meta property="og:title" content="' . e(self::title()) . "\">\n";
        $html .= '<meta property="og:description" content="' . e(self::description()) . "\">\n";
        $html .= '<meta property="og:url" content="' . e(self::canonical()) . "\">\n";
        $html .= '<meta property="og:image" content="' . e(self::ogImage()) . "\">\n";
        $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";

        foreach (self::$jsonLd as $bloco) {
            $html .= '<script type="application/ld+json">' . json_encode($bloco, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
        }

        return $html;
    }

    public static function breadcrumbItems(): array
    {
        return self::$breadcrumbItems;
    }

    public static function reset(): void
    {
        self::$title = '';
        self::$description = '';
        self::$canonical = '';
        self::$ogImage = '';
        self::$jsonLd = [];
        self::$breadcrumbItems = [];
    }
}
