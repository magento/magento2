<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Form\Element;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Exception\LocalizedException;

class AbstractSwatch extends Select
{
    /**
     * Get swatch values
     *
     * @return array
     * @throws LocalizedException
     */
    protected function getValues()
    {
        $options = [];
        $attribute = $this->getData('entity_attribute');
        if ($attribute instanceof Attribute) {
            $options = $attribute->getSource()->getAllOptions(true, true);
        }
        return $options;
    }
}
