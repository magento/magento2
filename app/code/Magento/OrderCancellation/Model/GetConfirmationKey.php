<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 */
declare(strict_types=1);

namespace Magento\OrderCancellation\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\OrderCancellation\Model\ResourceModel\SalesOrderConfirmCancel
    as SalesOrderConfirmCancelResourceModel;
use Magento\Sales\Model\Order;

class GetConfirmationKey
{
    /**
     * Length for confirmation key
     */
    public const CONFIRMATION_KEY_LENGTH = 32;

    /**
     * GetConfirmationKey Constructor
     *
     * @param SalesOrderConfirmCancelResourceModel $confirmationKeyResourceModel
     * @param Random $mathRandom
     */
    public function __construct(
        private readonly SalesOrderConfirmCancelResourceModel $confirmationKeyResourceModel,
        private readonly Random $mathRandom,
    ) {
    }

    /**
     * Returns confirmation key if exists, generates a new one otherwise
     *
     * @param Order $order
     * @param string $reason
     * @return string
     * @throws LocalizedException
     */
    public function execute(Order $order, string $reason): string
    {
        $confirmationKey = $this->confirmationKeyResourceModel->get((int)$order->getId());

        if (!$confirmationKey) {
            $confirmationKey['confirmation_key'] = $this->generateRandom();

            $this->confirmationKeyResourceModel->insert(
                (int)$order->getId(),
                $confirmationKey['confirmation_key'],
                $reason
            );
        }

        return $confirmationKey['confirmation_key'];
    }

    /**
     * Generates a random string
     *
     * @return string
     * @throws LocalizedException
     */
    private function generateRandom(): string
    {
        return $this->mathRandom->getRandomString(self::CONFIRMATION_KEY_LENGTH);
    }
}
