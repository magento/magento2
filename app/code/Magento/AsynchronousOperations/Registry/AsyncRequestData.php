<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Registry;

class AsyncRequestData
{
    /**
     * @var int
     */
    private int $isAuthorized = 0;

    /**
     * Set async request authorized
     *
     * @param int $isAuthorized
     * @return void
     */
    public function setAuthorized(int $isAuthorized): void
    {
        $this->isAuthorized = $isAuthorized;
    }

    /**
     * Get async request authorized
     *
     * @return int
     */
    public function isAuthorized(): int
    {
        return $this->isAuthorized;
    }
}
