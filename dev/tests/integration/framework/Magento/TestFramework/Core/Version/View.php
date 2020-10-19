<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Core\Version;

/**
 * Class for magento version flag.
 */
class View
{
    /**
     * Returns flag that checks that magento version is clean community version.
     *
     * @return bool
     */
    public function isVersionUpdated(): bool
    {
        return false;
    }
}
