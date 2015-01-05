<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Config source reports event store filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Model\Config\Source\Reports;

class Scope implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Scope filter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'website', 'label' => __('Website')],
            ['value' => 'group', 'label' => __('Store')],
            ['value' => 'store', 'label' => __('Store View')]
        ];
    }
}
