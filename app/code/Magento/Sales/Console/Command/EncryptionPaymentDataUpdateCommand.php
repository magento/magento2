<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Console\Cli;

/**
 * Command for updating encrypted credit card data to the latest cipher
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EncryptionPaymentDataUpdateCommand extends Command
{
    /** Command name */
    const NAME = 'encryption:payment-data:update';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\EncryptionUpdate
     */
    private $paymentResource;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Payment\EncryptionUpdate $paymentResource
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Payment\EncryptionUpdate $paymentResource
    ) {
        $this->paymentResource = $paymentResource;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription(
                'Re-encrypts encrypted credit card data with latest encryption cipher.'
            );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->paymentResource->reEncryptCreditCardNumbers();
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
