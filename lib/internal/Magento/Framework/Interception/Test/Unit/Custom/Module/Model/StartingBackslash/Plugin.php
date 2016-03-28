<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\StartingBackslash;

class Plugin
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'plugin_name';
    }
}
