<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Framework\App\Config\Value;
use Magento\Theme\Model\Design\BackendModelFactory;
use Magento\Theme\Model\Design\Config\ValueProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValueProcessorTest extends TestCase
{
    /** @var BackendModelFactory|MockObject */
    protected $backendModelFactory;

    /** @var Value|MockObject */
    protected $backendModel;

    /** @var ValueProcessor */
    protected $valueProcessor;

    protected function setUp(): void
    {
        $this->backendModelFactory = $this->getMockBuilder(BackendModelFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModel = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue', 'afterLoad'])
            ->getMock();

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
