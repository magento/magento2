<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\CodingStandard\Tool;

interface BlacklistInterface
{
    /**
     * Set list of paths to be excluded from tool run
     *
     * @param array $blackList
     * @return void
     */
    public function setBlackList(array $blackList);
}
