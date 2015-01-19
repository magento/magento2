<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleMSC\Model\Data;

use Magento\TestModuleMSC\Api\Data\ItemInterface;

class Item extends \Magento\Framework\Model\AbstractExtensibleModel
    implements ItemInterface
{
    /**
     * @return int
     */
    public function getItemId()
    {
        return $this->_data['item_id'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_data['name'];
    }
}
