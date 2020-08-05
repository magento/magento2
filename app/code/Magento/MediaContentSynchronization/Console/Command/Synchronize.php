<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronization\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Synchronize content with assets
 */
class Synchronize extends Command
{
    /**
     * @var SynchronizeInterface
     */
    private $synchronizeContent;

    /**
     * @var State $state
     */
    private $state;

    /**
     * @param SynchronizeInterface $synchronizeContent
     * @param State $state
     */
    public function __construct(
        SynchronizeInterface $synchronizeContent,
        State $state
    ) {
        $this->synchronizeContent = $synchronizeContent;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('media-content:sync');
        $this->setDescription('Synchronize content with assets');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Synchronizing content with assets...');
        $this->state->emulateAreaCode(
            Area::AREA_ADMINHTML,
            function () {
                $this->synchronizeContent->execute();
            }
        );
        $output->writeln('Completed content synchronization.');
        return Cli::RETURN_SUCCESS;
    }
}
