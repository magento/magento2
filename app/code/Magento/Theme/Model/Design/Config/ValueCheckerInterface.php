<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

interface ValueCheckerInterface
{
    /**
     * @return bool
     */
    public function isValueChanged();
}
