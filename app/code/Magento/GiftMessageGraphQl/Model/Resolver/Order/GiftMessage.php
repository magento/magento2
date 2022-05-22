<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessageGraphQl\Model\Resolver\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class for getting GiftMessage from CustomerOrder
 */
class GiftMessage implements ResolverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /** @var Uid */
    private $uidEncoder;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface|null $logger
     * @param Uid|null $uidEncoder
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger = null,
        Uid $uidEncoder = null
    ) {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->uidEncoder = $uidEncoder ?? ObjectManager::getInstance()->get(Uid::class);
    }

    /**
     * Return information about gift message for order
     *
     * @param Field            $field
     * @param ContextInterface $context
     * @param ResolveInfo      $info
     * @param array|null       $value
     * @param array|null       $args
     *
     * @return array|Value|mixed
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['id'])) {
            throw new GraphQlInputException(__('"id" value should be specified'));
        }

        $orderId = $this->uidEncoder->decode((string) $this->uidEncoder->encode((string) $value['id']));

        try {
            $orderGiftMessage = $this->orderRepository->get($orderId);
        } catch (LocalizedException $e) {
            $this->logger->error(__('Can\'t load gift message for order'));

            return null;
        }

        if (!$orderGiftMessage->getGiftMessageId()) {
            return null;
        }

        return [
            'to' => $orderGiftMessage->getRecipient() ?? '',
            'from' =>  $orderGiftMessage->getSender() ?? '',
            'message'=>  $orderGiftMessage->getMessage() ?? ''
        ];
    }
}
