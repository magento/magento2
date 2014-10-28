<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Stdlib;

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
        return array(array(array('б' => 2, 'в' => 3, 'а' => 1), 'ru_RU'));
    }

    /**
     * @covers \Magento\Framework\Stdlib\ArrayUtils::decorateArray
     */
    public function testDecorateArray()
    {
        $original = array(array('value' => 1), array('value' => 2), array('value' => 3));
        $decorated = array(
            array('value' => 1, 'is_first' => true, 'is_odd' => true),
            array('value' => 2, 'is_even' => true),
            array('value' => 3, 'is_last' => true, 'is_odd' => true)
        );

        // arrays
        $this->assertEquals($decorated, $this->_arrayUtils->decorateArray($original, ''));

        // \Magento\Framework\Object
        $sample = array(
            new \Magento\Framework\Object($original[0]),
            new \Magento\Framework\Object($original[1]),
            new \Magento\Framework\Object($original[2])
        );
        $decoratedVo = array(
            new \Magento\Framework\Object($decorated[0]),
            new \Magento\Framework\Object($decorated[1]),
            new \Magento\Framework\Object($decorated[2])
        );
        foreach ($decoratedVo as $obj) {
            $obj->setDataChanges(true); // hack for assertion
        }
        $this->assertEquals($decoratedVo, $this->_arrayUtils->decorateArray($sample, ''));
    }
}
