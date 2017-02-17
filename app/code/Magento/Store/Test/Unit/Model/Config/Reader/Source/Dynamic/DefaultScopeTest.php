<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config\Reader\Source\Dynamic;

use Magento\Framework\App\Config\Scope\Converter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\Config\Reader\Source\Dynamic\DefaultScope;
use Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory;

class DefaultScopeTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $expectedResult = [
            'config/key1' => 'default_db_value1',
            'config/key3' => 'default_db_value3',
        ];
        $collectionFactory = $this->getMockBuilder(ScopedFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionFactory->expects($this->once())
            ->method('create')
            ->with(['scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT])
            ->willReturn([
                new DataObject(['path' => 'config/key1', 'value' => 'default_db_value1']),
                new DataObject(['path' => 'config/key3', 'value' => 'default_db_value3']),
            ]);
        $converter = $this->getMockBuilder(Converter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $converter->expects($this->once())
            ->method('convert')
            ->with($expectedResult)
            ->willReturnArgument(0);
        $source = new DefaultScope(
            $collectionFactory,
            $converter
        );
        $this->assertEquals($expectedResult, $source->get());
    }
}
