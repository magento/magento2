<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Custom\Module\Model\ItemContainer;

class Enhanced extends \Magento\Framework\Interception\Custom\Module\Model\ItemContainer
{
    /**
     * @return string
     */
    public function getName()
    {
        return parent::getName() . '_enhanced';
    }
}
