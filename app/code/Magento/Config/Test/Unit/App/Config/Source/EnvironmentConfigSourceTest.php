<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\App\Config\Source;

use Magento\Config\App\Config\Source\EnvironmentConfigSource;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\Stdlib\ArrayManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EnvironmentConfigSourceTest extends TestCase
{
    /**
     * @var ArrayManager|MockObject
     */
    private $arrayManagerMock;

    /**
     * @var PlaceholderInterface|MockObject
     */
    private $placeholderMock;

    /**
     * @var EnvironmentConfigSource
     */
    private $source;

    protected function setUp(): void
    {
        $this->arrayManagerMock = $this->createMock(ArrayManager::class);
        $this->placeholderMock = $this->createMock(PlaceholderInterface::class);

        /** @var PlaceholderFactory|MockObject $placeholderFactoryMock */
        $placeholderFactoryMock = $this->createMock(PlaceholderFactory::class);
        $placeholderFactoryMock->expects($this->once())
            ->method('create')
            ->with(PlaceholderFactory::TYPE_ENVIRONMENT)
            ->willReturn($this->placeholderMock);

        $this->source = new EnvironmentConfigSource($this->arrayManagerMock, $placeholderFactoryMock);
    }

    /**
     * @param string $path
     * @param array|string $expectedResult
     */
    #[DataProvider('getDataProvider')]
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
    public static function getDataProvider()
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

    protected function tearDown(): void
    {
        unset($_ENV['CONFIG__UNIT__TEST__VALUE']);
    }
}
