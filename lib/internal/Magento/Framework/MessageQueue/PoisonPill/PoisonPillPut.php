<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\PoisonPill;

/**
 * Command interface describes how to create new version on poison pill.
 */
class PoisonPillPut implements PoisonPillPutInterface
{
    /**
     * First version of poison pill.
     *
     * @var string
     */
    private $firstVersion = '';

    /**
     * Stub implementation.
     *
     * @todo Will use cache storage after @MC-15997
     *
     * @return string
     */
    public function put(): string
    {
        return $this->firstVersion;
    }
}
