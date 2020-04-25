<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit;

use Magento\Framework\Cache\Config;
use Magento\Framework\Cache\Config\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    protected $_storage;

    /**
     * @var MockObject|Config
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_storage = $this->createPartialMock(Data::class, ['get']);
        $this->_model = new Config($this->_storage);
    }

    public function testGetTypes()
    {
        $this->_storage->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'types',
            []
        )->willReturn(
            ['val1', 'val2']
        );
        $result = $this->_model->getTypes();
        $this->assertCount(2, $result);
    }

    public function testGetType()
    {
        $this->_storage->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'types/someType',
            []
        )->willReturn(
            ['someTypeValue']
        );
        $result = $this->_model->getType('someType');
        $this->assertCount(1, $result);
    }
}
