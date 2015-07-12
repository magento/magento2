<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Meta
 */
class Meta extends AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('Magento\SalesSequence\Model\Resource\Meta');
    }
}
