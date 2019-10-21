<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Model;

use Magento\Framework\MessageQueue\PoisonPill\PoisonPillCompareInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillReadInterface;

/**
 * Poison pill compare
 */
class PoisonPillCompare implements PoisonPillCompareInterface
{
    /**
     * @var PoisonPillReadInterface
     */
    private $poisonPillRead;

    /**
     * PoisonPillCompare constructor.
     * @param PoisonPillReadInterface $poisonPillRead
     */
    public function __construct(
        PoisonPillReadInterface $poisonPillRead
    ) {
        $this->poisonPillRead = $poisonPillRead;
    }

    /**
     * @inheritdoc
     */
    public function isLatestVersion(string $poisonPillVersion): bool
    {
        return $poisonPillVersion === $this->poisonPillRead->getLatestVersion();
    }
}
