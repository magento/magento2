<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Currency\Import\Source;

use Magento\Directory\Model\Currency\Import\Config;
use Magento\Directory\Model\Currency\Import\Source\Service;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    /**
     * @var Service
     */
    protected $_model;

    /**
     * @var Config|MockObject
     */
    protected $_importConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_importConfig = $this->createMock(Config::class);
        $this->_model = new Service($this->_importConfig);
    }

    /**
     * @return void
     */
    public function testToOptionArray(): void
    {
        $this->_importConfig->expects(
            $this->once()
        )->method(
            'getAvailableServices'
        )->willReturn(
            ['service_one', 'service_two']
        );
        $this->_importConfig
            ->method('getServiceLabel')
            ->withConsecutive(['service_one'], ['service_two'])
            ->willReturnOnConsecutiveCalls('Service One', 'Service Two');
        $expectedResult = [
            ['value' => 'service_one', 'label' => 'Service One'],
            ['value' => 'service_two', 'label' => 'Service Two'],
        ];
        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
        // Makes sure the value is calculated only once
        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
    }
}
