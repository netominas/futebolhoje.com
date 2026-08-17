<?php

declare(strict_types=1);

final class AdminLigaController
{
    public function index(): void
    {
        AdminAuth::exigirLogin();

        $busca = trim((string) ($_GET['busca'] ?? ''));
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $resultado = Liga::paginado($busca, $pagina);

        View::render('admin/ligas', [
            'ligas' => $resultado['ligas'],
            'total' => $resultado['total'],
            'busca' => $busca,
            'pagina' => $pagina,
        ], 'admin');
    }

    public function destacar(array $params): void
    {
        AdminAuth::exigirLogin();
        AdminAuth::exigirCsrf();
        Liga::definirDestaque((int) $params['id'], true, Liga::proximaOrdemDestaque());
        $this->voltar();
    }

    public function remover(array $params): void
    {
        AdminAuth::exigirLogin();
        AdminAuth::exigirCsrf();
        Liga::definirDestaque((int) $params['id'], false, null);
        $this->voltar();
    }

    public function reordenar(array $params): void
    {
        AdminAuth::exigirLogin();
        AdminAuth::exigirCsrf();
        $ordem = max(1, (int) ($_POST['ordem'] ?? 1));
        Liga::definirDestaque((int) $params['id'], true, $ordem);
        $this->voltar();
    }

    public function ativarConteudoIa(array $params): void
    {
        AdminAuth::exigirLogin();
        AdminAuth::exigirCsrf();
        Liga::definirConteudoIa((int) $params['id'], true);
        $this->voltar();
    }

    public function desativarConteudoIa(array $params): void
    {
        AdminAuth::exigirLogin();
        AdminAuth::exigirCsrf();
        Liga::definirConteudoIa((int) $params['id'], false);
        $this->voltar();
    }

    private function voltar(): never
    {
        redirecionar($_POST['voltar'] ?? '/admin/ligas');
    }
}
