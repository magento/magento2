<?php
/**
 * Google AdWords conversion value type source
 */
declare(strict_types=1);
namespace Magento\GoogleAnalytics\Model\Config\Source;

class DefaultYes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get default yes option
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Magento\GoogleAnalytics\Helper\Data::DEFAULT_YES,
                'label' => __('Yes'),
            ]
        ];
    }
}
