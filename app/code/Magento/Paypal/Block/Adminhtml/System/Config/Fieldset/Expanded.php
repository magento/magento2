<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Fieldset;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Fieldset renderer which expanded by default
 * @since 2.0.0
 */
class Expanded extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * Whether is collapsed by default
     *
     * @var bool
     * @since 2.0.0
     */
    protected $isCollapsedDefault = true;
}
