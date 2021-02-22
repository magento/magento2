<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Test\Unit\Model\Config\Source;

class ValueTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GoogleAdwords\Model\Config\Source\ValueType
     */
    protected $_model;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManager->getObject(\Magento\GoogleAdwords\Model\Config\Source\ValueType::class, []);
    }

    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                [
                    'value' => \Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_TYPE_DYNAMIC,
                    'label' => 'Dynamic',
                ],
                [
                    'value' => \Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_TYPE_CONSTANT,
                    'label' => 'Constant'
                ],
            ],
            $this->_model->toOptionArray()
        );
    }
}
