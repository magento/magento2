<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
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
