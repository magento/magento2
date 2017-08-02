<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable;

/**
 * Class Bml
 * @since 2.0.0
 */
class Bml extends AbstractEnable
{
    /**
     * Getting the name of a UI attribute
     *
     * @return string
     * @since 2.0.0
     */
    protected function getDataAttributeName()
    {
        return 'bml';
    }
}
