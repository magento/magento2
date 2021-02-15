<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\PoisonPill;

/**
 * Command interface describes how to create new version on poison pill.
 * @api
 */
interface PoisonPillPutInterface
{
    /**
     * Put new version of poison pill inside DB.
     *
     * @return string
     * @throws \Exception
     */
    public function put(): string;
}
