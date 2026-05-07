<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\EncryptionKey\Model\Data\ReEncryptorList;

/**
 * Command for displaying a list of available data re-encryptors.
 */
class ListReEncryptorsCommand extends Command
{
    /**
     * @var ReEncryptorList
     */
    private ReEncryptorList $reEncryptorList;

    /**
     * @param ReEncryptorList $reEncryptorList
     */
    public function __construct(
        ReEncryptorList $reEncryptorList
    ) {
        $this->reEncryptorList = $reEncryptorList;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('encryption:data:list-re-encryptors');

        $this->setDescription(
            'Shows a list of available data re-encryptors.'
        );

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->reEncryptorList->getReEncryptors() as $name => $reEncryptor) {
            $output->writeln(
                sprintf(
                    '<fg=green>%-40s</> %s',
                    $name,
                    $reEncryptor->getDescription()
                )
            );
        }

        return Cli::RETURN_SUCCESS;
    }
}
