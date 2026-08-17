<?php

declare(strict_types=1);

final class AdminTimeController
{
    public function index(): void
    {
        AdminAuth::exigirLogin();

        $busca = trim((string) ($_GET['busca'] ?? ''));
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $resultado = Time::paginado($busca, $pagina);

        View::render('admin/times', [
            'times' => $resultado['times'],
            'total' => $resultado['total'],
            'busca' => $busca,
            'pagina' => $pagina,
        ], 'admin');
    }

    public function destacar(array $params): void
    {
        AdminAuth::exigirLogin();
        AdminAuth::exigirCsrf();
        Time::definirDestaque((int) $params['id'], true, Time::proximaOrdemDestaque());
        $this->voltar();
    }

    public function remover(array $params): void
    {
        AdminAuth::exigirLogin();
        AdminAuth::exigirCsrf();
        Time::definirDestaque((int) $params['id'], false, null);
        $this->voltar();
    }

    public function reordenar(array $params): void
    {
        AdminAuth::exigirLogin();
        AdminAuth::exigirCsrf();
        $ordem = max(1, (int) ($_POST['ordem'] ?? 1));
        Time::definirDestaque((int) $params['id'], true, $ordem);
        $this->voltar();
    }

    private function voltar(): never
    {
        redirecionar($_POST['voltar'] ?? '/admin/times');
    }
}
