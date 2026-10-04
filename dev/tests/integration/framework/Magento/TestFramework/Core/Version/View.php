<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
