<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Serialization;

use Magento\Framework\ObjectManagerInterface;

class DataBuilderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var DataBuilderFactory
     */
    private $dataBuilderFactory;

    public function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->setMethods([])
            ->getMock();

        $this->dataBuilderFactory = new DataBuilderFactory($this->objectManager);
    }

    /**
     * @param string $className
     * @param string $expectedBuilderName
     * @dataProvider classNamesDataProvider
     */
    public function testGetBuilderClassName($className, $expectedBuilderName)
    {
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with($expectedBuilderName)
            ->willReturn(new \StdClass);

        $this->assertInstanceOf('StdClass', $this->dataBuilderFactory->getDataBuilder($className));
    }

    /**
     * @return array
     */
    public function classNamesDataProvider()
    {
        return [
            ['\\TypeInterface', 'TypeDataBuilder'],
            ['\\Type', 'TypeBuilder']
        ];
    }
}
