<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model\OpaqueToken;

use Magento\Integration\Api\Data\UserTokenDataInterface;

class Data implements UserTokenDataInterface
{
    /**
     * @var \DateTimeImmutable
     */
    private $issued;

    /**
     * @var \DateTimeImmutable
     */
    private $expires;

    /**
     * @param \DateTimeImmutable $issued
     * @param \DateTimeImmutable $expires
     */
    public function __construct(\DateTimeImmutable $issued, \DateTimeImmutable $expires)
    {
        $this->issued = $issued;
        $this->expires = $expires;
    }

    public function getIssued(): \DateTimeImmutable
    {
        return $this->issued;
    }

    public function getExpires(): \DateTimeImmutable
    {
        return $this->expires;
    }
}
