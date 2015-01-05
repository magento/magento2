<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Integration\Model\Integration\Source;

/**
 * Integration status options.
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve status options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Magento\Integration\Model\Integration::STATUS_INACTIVE, 'label' => __('Inactive')],
            ['value' => \Magento\Integration\Model\Integration::STATUS_ACTIVE, 'label' => __('Active')]
        ];
    }
}
