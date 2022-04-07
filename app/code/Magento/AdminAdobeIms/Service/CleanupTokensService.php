<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\AdminAdobeIms\Model\ResourceModel\JwtUserToken\Revoked;

class CleanupTokensService
{
    private Revoked $revoked;

    /**
     * @param Revoked $revoked
     */
    public function __construct(Revoked $revoked)
    {
        $this->revoked = $revoked;
    }

    /**
     * return @void
     */
    public function execute()
    {
        $this->revoked->deleteAllRecords();
    }
}
