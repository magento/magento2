<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Developer\Console\Command;

use Magento\Developer\Model\HttpMethodUpdater\LogRepository;
use Magento\Developer\Model\HttpMethodUpdater\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;

/**
 * Update action classes for them to define accepted HTTP methods
 * based on logged data.
 */
class ApplyHttpMethodsCommand extends Command
{
    /**
     * @var LogRepository
     */
    private $logRepo;

    /**
     * @var Updater
     */
    private $updater;

    /**
     * @param LogRepository $logRepo
     * @param Updater $updater
     */
    public function __construct(
        LogRepository $logRepo,
        Updater $updater
    ) {
        parent::__construct();

        $this->logRepo = $logRepo;
        $this->updater = $updater;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dev:apply-http-methods')
            ->setDescription(
                'Update action classes for them to define accepted HTTP'
                .' methods based on logged data.'
            );

        $this->addArgument(
            'multiple',
            InputArgument::OPTIONAL,
            'Include action classes with different HTTP methods usages logged (y/n)',
            'n'
        );
        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("\nUpdating action classes...");
        $includeMultiple = $input->getArgument('multiple') === 'y' ? true : false;
        $logged = $this->logRepo->findLogged($includeMultiple);
        $output->writeln(count($logged) .' classes to update found');
        foreach ($logged as $item) {
            $this->updater->update($item);
        }
        $output->writeln('Updated!');

        return Cli::RETURN_SUCCESS;
    }
}
