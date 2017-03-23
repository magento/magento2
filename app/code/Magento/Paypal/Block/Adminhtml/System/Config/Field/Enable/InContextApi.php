<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable;

/**
 * Class InContextApi
 */
class InContextApi extends AbstractEnable
{
    /**
     * Getting the name of a UI attribute
     *
     * @return string
     */
    protected function getDataAttributeName()
    {
        return 'in-context-api';
    }
}
