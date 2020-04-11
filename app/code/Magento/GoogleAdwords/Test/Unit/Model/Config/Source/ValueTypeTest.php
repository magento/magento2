<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Test\Unit\Model\Config\Source;

use PHPUnit\Framework\TestCase;
use Magento\GoogleAdwords\Model\Config\Source\ValueType;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleAdwords\Helper\Data;

class ValueTypeTest extends TestCase
{
    /**
     * @var ValueType
     */
    protected $_model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(ValueType::class, []);
    }

    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                [
                    'value' => Data::CONVERSION_VALUE_TYPE_DYNAMIC,
                    'label' => 'Dynamic',
                ],
                [
                    'value' => Data::CONVERSION_VALUE_TYPE_CONSTANT,
                    'label' => 'Constant'
                ],
            ],
            $this->_model->toOptionArray()
        );
    }
}
