<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item;

class Enhanced extends \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item
{
    /**
     * @return string
     */
    public function getName()
    {
        return ucfirst(parent::getName());
    }
}
