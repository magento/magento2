<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

/**
 * Product form boolean field helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Boolean extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setValues([['label' => __('No'), 'value' => 0], ['label' => __('Yes'), 'value' => 1]]);
    }
}
