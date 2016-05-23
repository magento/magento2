<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit;

use \Magento\Framework\Stdlib\ArrayUtils;

/**
 * Magento\Framework\Stdlib\ArrayUtilsTest test case
 */
class ArrayUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Stdlib\ArrayUtils
     */
    protected $_arrayUtils;

    protected function setUp()
    {
        $this->_arrayUtils = new ArrayUtils();
    }

    /**
     * @covers \Magento\Framework\Stdlib\ArrayUtils::ksortMultibyte
     * @dataProvider ksortMultibyteDataProvider
     */
    public function testKsortMultibyte($input, $locale)
    {
        $this->_arrayUtils->ksortMultibyte($input, $locale);

        $iterator = 0;
        foreach ($input as $value) {
            $iterator++;
            $this->assertEquals($iterator, $value);
        }
    }

    /**
     * Data provider for ksortMultibyteDataProvider
     * @todo implement provider with values which different depends on locale
     */
    public function ksortMultibyteDataProvider()
    {
        return [[['б' => 2, 'в' => 3, 'а' => 1], 'ru_RU']];
    }

    /**
     * @covers \Magento\Framework\Stdlib\ArrayUtils::decorateArray
     */
    public function testDecorateArray()
    {
        $original = [['value' => 1], ['value' => 2], ['value' => 3]];
        $decorated = [
            ['value' => 1, 'is_first' => true, 'is_odd' => true],
            ['value' => 2, 'is_even' => true],
            ['value' => 3, 'is_last' => true, 'is_odd' => true],
        ];

        // arrays
        $this->assertEquals($decorated, $this->_arrayUtils->decorateArray($original, ''));

        // \Magento\Framework\DataObject
        $sample = [
            new \Magento\Framework\DataObject($original[0]),
            new \Magento\Framework\DataObject($original[1]),
            new \Magento\Framework\DataObject($original[2]),
        ];
        $decoratedVo = [
            new \Magento\Framework\DataObject($decorated[0]),
            new \Magento\Framework\DataObject($decorated[1]),
            new \Magento\Framework\DataObject($decorated[2]),
        ];
        $this->assertEquals($decoratedVo, $this->_arrayUtils->decorateArray($sample, ''));
    }
}
