<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Swatches\Model\Form\Element;

class AbstractSwatch extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * Get swatch values
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
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
