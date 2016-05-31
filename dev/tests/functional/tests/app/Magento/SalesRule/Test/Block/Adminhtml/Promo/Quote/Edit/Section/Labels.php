<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Ui\Test\Block\Adminhtml\Section;

/**
 * Backend sales rule label section.
 */
class Labels extends Section
{
    /**
     * Store label field name.
     */
    const STORE_LABEL_NAME = '[name="store_labels[%s]"]';

    /**
     * Fill data to labels fields on labels section.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function setFieldsData(array $fields, SimpleElement $element = null)
    {
        if (isset($fields['store_labels'])) {
            $count = 0;
            foreach ($fields['store_labels']['value'] as $storeLabel) {
                $element->find(sprintf(self::STORE_LABEL_NAME, $count))->setValue($storeLabel);
                ++$count;
            }
        }

        return $this;
    }

    /**
     * Get data of labels section.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldsData($fields = null, SimpleElement $element = null)
    {
        $context = $element === null ? $this->_rootElement : $element;
        $storeLabels = [];
        $count = 0;
        $field = $context->find(sprintf(self::STORE_LABEL_NAME, $count));
        while ($field->isVisible()) {
            $fieldValue = $field->getValue();
            if ($fieldValue != '') {
                $storeLabels[$count] = $fieldValue;
            }
            ++$count;
            $field = $context->find(sprintf(self::STORE_LABEL_NAME, $count));
        }

        return ['store_labels' => $storeLabels];
    }
}
