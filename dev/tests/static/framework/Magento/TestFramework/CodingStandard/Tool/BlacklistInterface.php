<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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