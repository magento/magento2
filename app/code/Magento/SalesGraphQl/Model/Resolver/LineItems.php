<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Api\Data\InvoiceInterface as Invoice;
use Magento\Sales\Model\Order;
use Magento\SalesGraphQl\Model\Resolver\LineItem\DataProvider as LineItemProvider;

/**
 * Resolver for Line Items (Invoice Items, Shipment Items)
 */
class LineItems implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var LineItemProvider
     */
    private $lineItemProvider;

    /**
     * @param ValueFactory $valueFactory
     * @param LineItemProvider $lineItemProvider
     */
    public function __construct(ValueFactory $valueFactory, LineItemProvider $lineItemProvider)
    {
        $this->valueFactory = $valueFactory;
        $this->lineItemProvider = $lineItemProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model']) || !($value['model'] instanceof Invoice)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        if (!isset($value['order']) || !($value['order'] instanceof Order)) {
            throw new LocalizedException(__('"order" value should be specified'));
        }

        /** @var ExtensibleDataInterface $lineItemModel */
        $lineItemModel = $value['model'];
        $parentOrder = $value['order'];

        return $this->valueFactory->create(
            $this->lineItemProvider->getLineItems($parentOrder, $lineItemModel->getItems())
        );
    }
}
