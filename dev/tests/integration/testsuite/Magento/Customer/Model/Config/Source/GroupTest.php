<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config\Source;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class \Magento\Customer\Model\Config\Source\Group
 */
class GroupTest extends \PHPUnit\Framework\TestCase
{
    public function testToOptionArray()
    {
        /** @var Group $group */
        $group = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Config\Source\Group::class);
        $options = $group->toOptionArray();
        $this->assertContainsOptionRecursive('', '-- Please Select --', $options);
    }

    private function assertContainsOptionRecursive($expectedValue, $expectedLabel, array $values)
    {
        $this->assertTrue(
            $this->hasOptionLabelRecursive($expectedValue, $expectedLabel, $values),
            'Label ' . $expectedLabel . ' not found'
        );
    }

    private function hasOptionLabelRecursive($value, $label, array $values)
    {
        $hasLabel = false;
        foreach ($values as $option) {
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('value', $option);
            if (strpos((string)$option['label'], (string)$label) !== false) {
                $this->assertEquals($value, $option['value']);
                $hasLabel = true;
                break;
            } elseif (is_array($option['value'])) {
                $hasLabel |= $this->hasOptionLabelRecursive($value, $label, $option['value']);
            }
        }

        return (bool)$hasLabel;
    }
}
