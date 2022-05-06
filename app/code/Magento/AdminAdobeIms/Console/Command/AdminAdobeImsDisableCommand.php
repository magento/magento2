<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Console\Command;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to set Admin Adobe IMS Module mode
 */
class AdminAdobeImsDisableCommand extends Command
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var TypeListInterface
     */
    private TypeListInterface $cacheTypeList;

    /**
     * @param ImsConfig $adminImsConfig
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        ImsConfig $adminImsConfig,
        TypeListInterface $cacheTypeList
    ) {
        parent::__construct();
        $this->adminImsConfig = $adminImsConfig;

        $this->setName('admin:adobe-ims:disable')
            ->setDescription('Disable Adobe IMS Module');
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $this->adminImsConfig->disableModule();
            $this->cacheTypeList->cleanType(Config::TYPE_IDENTIFIER);
            $output->writeln(__('Admin Adobe IMS integration is disabled'));

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            return Cli::RETURN_FAILURE;
        }
    }
}
