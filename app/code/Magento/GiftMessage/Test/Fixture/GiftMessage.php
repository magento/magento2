<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\GiftMessage\Model\ResourceModel\Message;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class GiftMessage implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'sender' => 'Romeo',
        'recipient' => 'Mercutio',
        'message' => 'Fixture Test message.',
    ];

    /**
     * @param MessageFactory $giftMessageFactory
     * @param Message $messageResourceModel
     * @param DataMerger $dataMerger
     */
    public function __construct(
        private readonly MessageFactory $giftMessageFactory,
        private readonly Message $messageResourceModel,
        private readonly DataMerger $dataMerger,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->dataMerger->merge(self::DEFAULT_DATA, $data);
        $message = $this->giftMessageFactory->create();
        $message
            ->setSender($data['sender'])
            ->setRecipient($data['recipient'])
            ->setMessage($data['message']);

        $this->messageResourceModel->save($message);

        return $message;
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $this->messageResourceModel->delete($data);
    }
}
