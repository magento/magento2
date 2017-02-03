<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Backend sales rule label tab.
 */
class Labels extends Tab
{
    /**
     * Store label field name.
     */
    const STORE_LABEL_NAME = '[name="store_labels[%s]"]';

    /**
     * Fill data to labels fields on labels tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
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
     * Get data of labels tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDataFormTab($fields = null, SimpleElement $element = null)
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
