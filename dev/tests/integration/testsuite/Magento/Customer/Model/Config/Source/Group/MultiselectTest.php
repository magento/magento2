<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config\Source\Group;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class \Magento\Customer\Model\Config\Source\Group\Multiselect
 */
class MultiselectTest extends \PHPUnit\Framework\TestCase
{
    public function testToOptionArray()
    {
        /** @var Multiselect $multiselect */
        $multiselect = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Model\Config\Source\Group\Multiselect::class
        );

        $options = $multiselect->toOptionArray();
        $optionsToCompare = [];
        foreach ($options as $option) {
            if (is_array($option['value'])) {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $optionsToCompare = array_merge($optionsToCompare, $option['value']);
            } else {
                $optionsToCompare[] = $option;
            }
        }
        sort($optionsToCompare);
        foreach ($optionsToCompare as $item) {
            $this->assertTrue(
                in_array(
                    $item,
                    [
                        [
                            'value' => 1,
                            'label' => 'Default (General)',
                        ],
                        [
                            'value' => 1,
                            'label' => 'General',
                        ],
                        [
                            'value' => 2,
                            'label' => 'Wholesale',
                        ],
                        [
                            'value' => 3,
                            'label' => 'Retailer',
                        ],
                    ]
                )
            );
        }
    }
}
