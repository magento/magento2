<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable;

/**
 * Class InContext
 * @since 2.1.0
 */
class InContext extends AbstractEnable
{
    /**
     * Getting the name of a UI attribute
     *
     * @return string
     * @since 2.1.0
     */
    protected function getDataAttributeName()
    {
        return 'in-context';
    }
}
