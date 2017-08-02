<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Source\Import;

/**
 * Source import behavior model
 *
 * @api
 * @since 2.0.0
 */
abstract class AbstractBehavior implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get array of possible values
     *
     * @abstract
     * @return array
     * @since 2.0.0
     */
    abstract public function toArray();

    /**
     * Prepare and return array of option values
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $optionArray = [['label' => __('-- Please Select --'), 'value' => '']];
        $options = $this->toArray();
        if (is_array($options) && count($options) > 0) {
            foreach ($options as $value => $label) {
                $optionArray[] = ['label' => $label, 'value' => $value];
            }
        }
        return $optionArray;
    }

    /**
     * Get current behaviour group code
     *
     * @abstract
     * @return string
     * @since 2.0.0
     */
    abstract public function getCode();

    /**
     * Get array of notes for possible values
     *
     * @param string $entityCode
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getNotes($entityCode)
    {
        return [];
    }
}
