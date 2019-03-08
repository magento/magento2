<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Model;

use Magento\MessageQueue\Api\Data\PoisonPillInterface;

class PoisonPillFactory
{
    /**
     * @var PoisonPillInterface
     */
    private $poisonPill;

    /**
     * @param PoisonPillInterface $poisonPill
     */
    public function __construct(
        PoisonPillInterface $poisonPill
    ) {
        $this->poisonPill = $poisonPill;
    }

    /**
     * @param int $version
     * @return PoisonPillInterface
     */
    public function create(int $version): PoisonPillInterface
    {

    }
}
