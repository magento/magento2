<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleAdwords\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleAdwords\Helper\Data;
use Magento\GoogleAdwords\Model\Config\Source\ValueType;
use PHPUnit\Framework\TestCase;

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
