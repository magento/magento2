<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category\Attribute\Source;

/**
 * Catalog category landing page attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mode extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => \Magento\Catalog\Model\Category::DM_PRODUCT, 'label' => __('Products only')],
                ['value' => \Magento\Catalog\Model\Category::DM_PAGE, 'label' => __('Static block only')],
                ['value' => \Magento\Catalog\Model\Category::DM_MIXED, 'label' => __('Static block and products')],
            ];
        }
        return $this->_options;
    }
}
