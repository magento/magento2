<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\CreditMemo;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;

/**
 * Resolve credit memo comments
 */
class CreditMemoComments implements ResolverInterface
{
    /**
     * @param TimezoneInterface|null $timezone
     */
    public function __construct(
        private ?TimezoneInterface $timezone = null
    ) {
        $this->timezone = $timezone ?: ObjectManager::getInstance()->get(TimezoneInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
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
                    'timestamp' => $this->timezone->date($comment->getCreatedAt())
                        ->format(DateTime::DATETIME_PHP_FORMAT)
                ];
            }
        }

        return $comments;
    }
}
