<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
