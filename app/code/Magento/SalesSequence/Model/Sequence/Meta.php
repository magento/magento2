<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\SalesSequence\Model\Sequence;

use Magento\Sales\Model\AbstractModel;

/**
 * Class Meta
 */
class Meta extends AbstractModel
{
    /**
     * Initialization
     */
    protected function _construct()
    {

        $this->_init('Magento\SalesSequence\Model\Resource\Sequence\Meta');
    }
}