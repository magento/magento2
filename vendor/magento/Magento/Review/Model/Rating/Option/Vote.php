<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Review\Model\Rating\Option;

/**
 * Rating vote model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @codeCoverageIgnore
 */
class Vote extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Review\Model\Resource\Rating\Option\Vote');
    }
}
