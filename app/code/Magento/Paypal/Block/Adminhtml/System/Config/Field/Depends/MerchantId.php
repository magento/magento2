<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field\Depends;

use Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable\AbstractEnable;

/**
 * Class MerchantId
 */
class MerchantId extends AbstractEnable
{
    /**
     * Getting the name of a UI attribute
     *
     * @return string
     */
    protected function getDataAttributeName()
    {
        return 'merchant-id';
    }
}
