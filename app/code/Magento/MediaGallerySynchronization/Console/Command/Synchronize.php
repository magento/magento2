<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\MediaGallerySynchronizationApi\Api\SynchronizeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Synchronize files in media storage and media assets database records
 */
class Synchronize extends Command
{
    /**
     * @var SynchronizeInterface
     */
    private $synchronizeAssets;

    /**
     * @param SynchronizeInterface $synchronizeAssets
     */
    public function __construct(
        SynchronizeInterface $synchronizeAssets
    ) {
        $this->synchronizeAssets = $synchronizeAssets;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('media-gallery:sync');
        $this->setDescription(
            'Synchronize media storage and media assets in the database'
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Synchronizing assets information from media storage to database...');

        $this->synchronizeAssets->execute();

        $output->writeln('Completed assets synchronization.');

        return Cli::RETURN_SUCCESS;
    }
}
