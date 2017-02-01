<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\App\Config\Source;

use Magento\Config\App\Config\Source\EnvironmentConfigSource;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\Stdlib\ArrayManager;

class EnvironmentConfigSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ArrayManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $arrayManagerMock;

    /**
     * @var PlaceholderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeholderMock;

    /**
     * @var EnvironmentConfigSource
     */
    private $source;

    protected function setUp()
    {
        $this->arrayManagerMock = $this->getMockBuilder(ArrayManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->placeholderMock = $this->getMockBuilder(PlaceholderInterface::class)
            ->getMockForAbstractClass();

        /** @var PlaceholderFactory|\PHPUnit_Framework_MockObject_MockObject $placeholderFactoryMock */
        $placeholderFactoryMock = $this->getMockBuilder(PlaceholderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $placeholderFactoryMock->expects($this->once())
            ->method('create')
            ->with(PlaceholderFactory::TYPE_ENVIRONMENT)
            ->willReturn($this->placeholderMock);

        $this->source = new EnvironmentConfigSource($this->arrayManagerMock, $placeholderFactoryMock);
    }

    public function testGet()
    {
        $placeholder = 'CONFIG__UNIT__TEST__VALUE';
        $value = 'test_value';
        $path = 'unit/test/value';
        $expectedArray = ['unit' => ['test' => ['value' => $value]]];
        $_ENV[$placeholder] = $value;

        $this->placeholderMock->expects($this->any())
            ->method('isApplicable')
            ->willReturnMap([
                [$placeholder, true]
            ]);
        $this->placeholderMock->expects($this->once())
            ->method('restore')
            ->with($placeholder)
            ->willReturn($path);
        $this->arrayManagerMock->expects($this->once())
            ->method('set')
            ->with($path, [], $value)
            ->willReturn($expectedArray);

        $this->assertSame($expectedArray, $this->source->get());
    }
}
