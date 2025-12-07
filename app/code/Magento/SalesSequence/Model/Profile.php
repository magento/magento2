<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesSequence\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Profile
 *
 * @api
 * @method string getPrefix()
 * @method string getSuffix()
 * @method int getStartValue()
 * @method int getStep()
 * @since 100.0.2
 */
class Profile extends AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\SalesSequence\Model\ResourceModel\Profile::class);
    }
}
