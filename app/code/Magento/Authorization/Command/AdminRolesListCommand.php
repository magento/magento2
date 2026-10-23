<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Authorization\Command;

use Magento\Authorization\Model\GetAdminRolesList;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command listing admin role IDs and names
 */
class AdminRolesListCommand extends Command
{
    /**
     * @var GetAdminRolesList
     */
    private $getAdminRolesListService;

    /**
     * @param GetAdminRolesList $getAdminRolesListService
     * @param string|null $name
     */
    public function __construct(
        GetAdminRolesList $getAdminRolesListService,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->getAdminRolesListService = $getAdminRolesListService;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName("admin:role:list");
        $this->setDescription("Shows all existing admin role ids and names");
        parent::configure();
    }

    /**
     * Provides table with admin roles
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Existing admin roles:');
        $table = new Table($output);
        $table->setHeaders(['Role ID', 'Role Name']);
        $table->setRows($this->getAdminRolesListService->execute());
        $table->render();

        return Cli::RETURN_SUCCESS;
    }
}
