<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Attribute;

use Magento\Catalog\Model\Attribute\Config;
use Magento\Catalog\Model\Attribute\Config\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $_model;

    /**
     * @var Data|MockObject
     */
    protected $_dataStorage;

    protected function setUp(): void
    {
        $this->_dataStorage = $this->createPartialMock(Data::class, ['get']);
        $this->_model = new Config($this->_dataStorage);
    }

    public function testGetAttributeNames()
    {
        $expectedResult = ['fixture_attribute_one', 'fixture_attribute_two'];
        $this->_dataStorage->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'some_group'
        )->willReturn(
            $expectedResult
        );
        $this->assertSame($expectedResult, $this->_model->getAttributeNames('some_group'));
    }
}
