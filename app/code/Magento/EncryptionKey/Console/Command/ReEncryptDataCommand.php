<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Console\Command;

use DateInterval;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\EncryptionKey\Model\Data\ReEncryptorList;

/**
 * Command for re-encryption of encrypted data using current encryption key.
 */
class ReEncryptDataCommand extends Command
{
    /**
     * @var string
     */
    private const INPUT_KEY_ENCRYPTORS = 'encryptors';

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
        $this->setName('encryption:data:re-encrypt');

        $this->setDescription(
            'Re-encrypts encrypted data using current encryption key.'
        );

        $this->addArgument(
            self::INPUT_KEY_ENCRYPTORS,
            InputArgument::IS_ARRAY,
            'Space-separated list of re-encryptors to use.'
        );

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $requestedReEncryptorsNames = $input->getArgument(
            self::INPUT_KEY_ENCRYPTORS
        );

        $availableReEncryptors = $this->reEncryptorList->getReEncryptors();

        if (empty($requestedReEncryptorsNames)) {
            $requestedReEncryptorsNames = array_keys($availableReEncryptors);
        }

        foreach ($requestedReEncryptorsNames as $name) {
            if (!isset($availableReEncryptors[$name])) {
                $output->writeLn(
                    sprintf("<fg=red>Re-encryptor '%s' could not be found!</>", $name)
                );

                continue;
            }

            $reEncryptor = $availableReEncryptors[$name];

            $output->writeLn(
                sprintf("Executing '%s' re-encryptor...", $name)
            );

            try {
                $startTime = new \DateTimeImmutable();

                $errors = $reEncryptor->reEncrypt();

                $endTime = new \DateTimeImmutable();

                $elapsedTime = $this->formatInterval(
                    $startTime->diff($endTime)
                );
            } catch (\Throwable $e) {
                $output->writeLn("<fg=red>Failed due to the following error:</>");

                $output->writeLn(
                    sprintf("<fg=white;bg=red>%s</>", $e->getMessage())
                );

                continue;
            }

            if (empty($errors)) {
                $output->writeLn(
                    sprintf(
                        "<fg=green>Done successfully in %s.</>",
                        $elapsedTime
                    )
                );
            } else {
                $output->writeLn(
                    sprintf(
                        "<fg=yellow>Done in %s but with the following errors:</>",
                        $elapsedTime
                    )
                );

                foreach ($errors as $error) {
                    $output->writeLn(
                        sprintf(
                            "<fg=black;bg=yellow>[%s %s]: %s</>",
                            $error->getRowIdField(),
                            $error->getRowIdValue(),
                            $error->getMessage()
                        )
                    );
                }
            }
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Formats a date interval.
     *
     * @param DateInterval $interval
     *
     * @return string
     */
    private function formatInterval(DateInterval $interval): string
    {
        $days = (int) $interval->format('%d');

        $hours = $days * 24 + (int) $interval->format('%H');
        $minutes = $interval->format('%I');
        $seconds = $interval->format('%S');

        return sprintf("%s:%s:%s", $hours, $minutes, $seconds);
    }
}
