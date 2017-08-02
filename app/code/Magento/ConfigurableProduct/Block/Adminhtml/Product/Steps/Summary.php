<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps;

/**
 * @api
 * @since 2.0.0
 */
class Summary extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCaption()
    {
        return __('Summary');
    }

    /**
     * Get url to upload files
     *
     * @return string
     * @since 2.0.0
     */
    public function getImageUploadUrl()
    {
        return $this->getUrl('catalog/product_gallery/upload');
    }
}
