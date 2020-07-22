<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Resolve credit memos for order
 */
class CreditMemos implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!(($value['model'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var OrderInterface $orderModel */
        $orderModel = $value['model'];

        $creditMemos = [];
        /** @var CreditmemoInterface $creditMemo */
        foreach ($orderModel->getCreditmemosCollection() as $creditMemo) {
            $creditMemos[] = [
                'id' => base64_encode($creditMemo->getEntityId()),
                'number' => $creditMemo->getIncrementId(),
                'order' => $orderModel,
                'model' => $creditMemo
            ];
        }
        return $creditMemos;
    }
}
