<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

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
