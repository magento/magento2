<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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