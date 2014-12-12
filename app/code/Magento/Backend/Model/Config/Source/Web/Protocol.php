<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Model\Config\Source\Web;

class Protocol implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => ''],
            ['value' => 'http', 'label' => __('HTTP (unsecure)')],
            ['value' => 'https', 'label' => __('HTTPS (SSL)')]
        ];
    }
}
