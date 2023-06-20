<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\CreditMemo;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;

/**
 * Resolve credit memo comments
 */
class CreditMemoComments implements ResolverInterface
{

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

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
        if (!(($value['model'] ?? null) instanceof CreditmemoInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var CreditmemoInterface $creditMemo */
        $creditMemo = $value['model'];

        $comments = [];
        foreach ($creditMemo->getComments() as $comment) {
            if ($comment->getIsVisibleOnFront()) {
                $comments[] = [
                    'message' => $comment->getComment(),
                    'timestamp' => $this->getFormatDate($comment->getCreatedAt())
                ];
            }
        }

        return $comments;
    }

    /**
     * Retrieve the timezone date
     *
     * @param string $date
     * @return string
     */
    public function getFormatDate(string $date): string
    {
        return $this->timezone->date(
            $date
        )->format('Y-m-d H:i:s');
    }
}
