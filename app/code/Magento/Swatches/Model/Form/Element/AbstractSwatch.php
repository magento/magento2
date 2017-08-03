<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Form\Element;

/**
 * Class \Magento\Swatches\Model\Form\Element\AbstractSwatch
 *
 * @since 2.0.0
 */
class AbstractSwatch extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * Get swatch values
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function getValues()
    {
        $options = [];
        $attribute = $this->getData('entity_attribute');
        if ($attribute instanceof \Magento\Catalog\Model\ResourceModel\Eav\Attribute) {
            $options = $attribute->getSource()->getAllOptions(true, true);
        }
        return $options;
    }
}
