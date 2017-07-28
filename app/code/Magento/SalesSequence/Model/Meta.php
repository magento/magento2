<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Meta
 *
 * @api
 * @since 2.0.0
 */
class Meta extends AbstractModel
{
    /**
     * @inheritdoc
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\SalesSequence\Model\ResourceModel\Meta::class);
    }
}
