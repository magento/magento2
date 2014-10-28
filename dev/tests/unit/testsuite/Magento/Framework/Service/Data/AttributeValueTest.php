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
namespace Magento\Framework\Service\Data;

use Magento\Framework\Validator\Test\True;

class AttributeValueTest extends \PHPUnit_Framework_TestCase
{
    const ATTRIBUTE_CODE = 'ATTRIBUTE_CODE';

    const STRING_VALUE = 'VALUE';

    const INTEGER_VALUE = 1;

    const FLOAT_VALUE = 1.0;

    const BOOLEAN_VALUE = true;

    public function testConstructorAndGettersWithString()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $attributeBuilder = $helper->getObject('\Magento\Framework\Service\Data\AttributeValueBuilder')
            ->setAttributeCode(self::ATTRIBUTE_CODE)
            ->setValue(self::STRING_VALUE);
        $attribute = new AttributeValue($attributeBuilder);

        $this->assertSame(self::ATTRIBUTE_CODE, $attribute->getAttributeCode());
        $this->assertSame(self::STRING_VALUE, $attribute->getValue());
    }

    public function testConstructorAndGettersWithInteger()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $attributeBuilder = $helper->getObject('\Magento\Framework\Service\Data\AttributeValueBuilder')
            ->setAttributeCode(self::ATTRIBUTE_CODE)
            ->setValue(self::INTEGER_VALUE);
        $attribute = new AttributeValue($attributeBuilder);

        $this->assertSame(self::ATTRIBUTE_CODE, $attribute->getAttributeCode());
        $this->assertSame(self::INTEGER_VALUE, $attribute->getValue());
    }

    public function testConstructorAndGettersWithFloat()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $attributeBuilder = $helper->getObject('\Magento\Framework\Service\Data\AttributeValueBuilder')
            ->setAttributeCode(self::ATTRIBUTE_CODE)
            ->setValue(self::FLOAT_VALUE);
        $attribute = new AttributeValue($attributeBuilder);

        $this->assertSame(self::ATTRIBUTE_CODE, $attribute->getAttributeCode());
        $this->assertSame(self::FLOAT_VALUE, $attribute->getValue());
    }

    public function testConstructorAndGettersWithBoolean()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $attributeBuilder = $helper->getObject('\Magento\Framework\Service\Data\AttributeValueBuilder')
            ->setAttributeCode(self::ATTRIBUTE_CODE)
            ->setValue(self::BOOLEAN_VALUE);
        $attribute = new AttributeValue($attributeBuilder);

        $this->assertSame(self::ATTRIBUTE_CODE, $attribute->getAttributeCode());
        $this->assertSame(self::BOOLEAN_VALUE, $attribute->getValue());
    }
}
