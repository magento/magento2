<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorization\Command;

use Magento\Authorization\Model\GetAdminRolesList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableFactory;

/**
 * Class AdminRolesListCommand is cli command for showing all existing admin role ids and names
 */
class AdminRolesListCommand extends Command
{
    /**
     * @var GetAdminRolesList
     */
    private $getAdminRolesListService;

    /**
     * @var TableFactory
     */
    private $tableFactory;

    /**
     * @param GetAdminRolesList $getAdminRolesListService
     * @param TableFactory $tableFactory
     * @param string|null $name
     */
    public function __construct(
        GetAdminRolesList $getAdminRolesListService,
        TableFactory $tableFactory,
        string $name = null
    ) {
        parent::__construct($name);
        $this->getAdminRolesListService = $getAdminRolesListService;
        $this->tableFactory = $tableFactory;
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Existing admin roles:');
        $table = $this->tableFactory->create(['output' => $output]);
        $table->setHeaders(['Role ID', 'Role Name']);
        $table->setRows($this->getAdminRolesListService->execute());
        $table->render();
    }
}
