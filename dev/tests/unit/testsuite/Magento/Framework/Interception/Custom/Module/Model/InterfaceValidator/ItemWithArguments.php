<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator;

class ItemWithArguments
{
    /**
     * @param string $name
     * @return string
     */
    public function getItem($name = 'default')
    {
        return $name;
    }
}
