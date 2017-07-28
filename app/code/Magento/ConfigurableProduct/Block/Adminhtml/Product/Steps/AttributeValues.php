<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps;

/**
 * Adminhtml block for fieldset of configurable product
 *
 * @api
 * @since 2.0.0
 */
class AttributeValues extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCaption()
    {
        return __('Attribute Values');
    }
}
