<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\SalesSequence\Model\Sequence;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Profile
 */
class Profile extends AbstractModel
{
    /**
     * Initialization
     */
    protected function _construct()
    {
        $this->_init('Magento\SalesSequence\Model\Resource\Sequence\Profile');
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getData('is_active');
    }
}
