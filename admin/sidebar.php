<?php
/**
 * Sidebar Admin
 * Hotel Mucinga Nzambi
 */

$current_page = basename($_SERVER['PHP_SELF']);

?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="bi bi-building"></i> <?= SYSTEM_NAME ?></h3>
        <small>Painel Administrativo</small>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="solicitacoes.php" class="<?= $current_page === 'solicitacoes.php' ? 'active' : '' ?>">
                <i class="bi bi-inbox"></i> Solicitações
            </a>
        </li>
        <li>
            <a href="reservas.php" class="<?= $current_page === 'reservas.php' ? 'active' : '' ?>">
                <i class="bi bi-calendar-check"></i> Reservas
            </a>
        </li>
        <?php if (RBAC::isAdmin()): ?>
            <li>
                <a href="quartos.php" class="<?= $current_page === 'quartos.php' ? 'active' : '' ?>">
                    <i class="bi bi-door-open"></i> Quartos
                </a>
            </li>
            <li>
                <a href="tipos_quarto.php" class="<?= $current_page === 'tipos_quarto.php' ? 'active' : '' ?>">
                    <i class="bi bi-house"></i> Tipos de Quarto
                </a>
            </li>
            <li>
                <a href="tarifas.php" class="<?= $current_page === 'tarifas.php' ? 'active' : '' ?>">
                    <i class="bi bi-currency-exchange"></i> Tarifas
                </a>
            </li>
            <li>
                <a href="servicos.php" class="<?= $current_page === 'servicos.php' ? 'active' : '' ?>">
                    <i class="bi bi-list-ul"></i> Serviços
                </a>
            </li>
            <li>
                <a href="bancos.php" class="<?= $current_page === 'bancos.php' ? 'active' : '' ?>">
                    <i class="bi bi-bank"></i> Bancos
                </a>
            </li>
            <li>
                <a href="usuarios.php" class="<?= $current_page === 'usuarios.php' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Usuários
                </a>
            </li>
            <li>
                <a href="relatorios.php" class="<?= $current_page === 'relatorios.php' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up"></i> Relatórios
                </a>
            </li>
        <?php endif; ?>
        <li>
            <a href="../logout.php">
                <i class="bi bi-box-arrow-right"></i> Sair
            </a>
        </li>
    </ul>
</div>

