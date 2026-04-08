<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Framework\App\Config\Value;
use Magento\Theme\Model\Design\BackendModelFactory;
use Magento\Theme\Model\Design\Config\ValueProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\TestCase;

class ValueProcessorTest extends TestCase
{
    use MockCreationTrait;

    /** @var BackendModelFactory|MockObject */
    protected $backendModelFactory;

    /** @var Value|MockObject */
    protected $backendModel;

    /** @var ValueProcessor */
    protected $valueProcessor;

    protected function setUp(): void
    {
        $this->backendModelFactory = $this->createMock(BackendModelFactory::class);
        $this->backendModel = $this->createPartialMockWithReflection(
            Value::class,
            ['getValue', 'afterLoad']
        );

        $this->valueProcessor = new ValueProcessor($this->backendModelFactory);
    }

    public function testProcess()
    {
        $path = 'design/head/logo';
        $value = 'path/to/logo';
        $scope = 'websites';
        $scopeId = 1;

        $this->backendModelFactory->expects($this->once())
            ->method('createByPath')
            ->with(
                $path,
                [
                    'value' => $value,
                    'field_config' => ['path' => $path],
                    'scope' => $scope,
                    'scope_id' => $scopeId
                ]
            )
            ->willReturn($this->backendModel);
        $this->backendModel->expects($this->once())
            ->method('afterLoad');
        $this->backendModel->expects($this->once())
            ->method('getValue')
            ->willReturn($value);
        $this->assertEquals($value, $this->valueProcessor->process($value, $scope, $scopeId, ['path' => $path]));
    }
}
