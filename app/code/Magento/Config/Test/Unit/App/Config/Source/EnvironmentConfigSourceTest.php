<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\App\Config\Source;

use Magento\Config\App\Config\Source\EnvironmentConfigSource;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\Stdlib\ArrayManager;

class EnvironmentConfigSourceTest extends \PHPUnit\Framework\TestCase
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

    /**
     * @param string $path
     * @param array|string $expectedResult
     * @dataProvider getDataProvider
     */
    public function testGet($path, $expectedResult)
    {
        $placeholder = 'CONFIG__UNIT__TEST__VALUE';
        $configValue = 'test_value';
        $configPath = 'unit/test/value';
        $expectedArray = ['unit' => ['test' => ['value' => $configValue]]];
        $_ENV[$placeholder] = $configValue;

        $this->placeholderMock->expects($this->any())
            ->method('isApplicable')
            ->willReturnMap([
                [$placeholder, true]
            ]);
        $this->placeholderMock->expects($this->once())
            ->method('restore')
            ->with($placeholder)
            ->willReturn($configPath);
        $this->arrayManagerMock->expects($this->once())
            ->method('set')
            ->with($configPath, [], $configValue)
            ->willReturn($expectedArray);

        $this->assertEquals($expectedResult, $this->source->get($path));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            ['', ['unit' => ['test' => ['value' => 'test_value']]]],
            ['unit', ['test' => ['value' => 'test_value']]],
            ['unit/test', ['value' => 'test_value']],
            ['unit/test/value', 'test_value'],
            ['wrong/path', []],
        ];
    }

    public function testGetWithoutEnvConfigurationVariables()
    {
        $expectedArray = [];

        $this->placeholderMock->expects($this->any())
            ->method('isApplicable')
            ->willReturn(false);
        $this->placeholderMock->expects($this->never())
            ->method('restore');
        $this->arrayManagerMock->expects($this->never())
            ->method('set');

        $this->assertSame($expectedArray, $this->source->get());
    }

    public function tearDown()
    {
        unset($_ENV['CONFIG__UNIT__TEST__VALUE']);
    }
}
