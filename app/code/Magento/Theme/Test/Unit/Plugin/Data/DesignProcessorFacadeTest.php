<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Plugin\Data;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Theme\Plugin\DesignProcessorFacade;
use Magento\Config\Console\Command\ConfigSet\ProcessorFacade;
use Magento\Framework\Exception\LocalizedException;
use Magento\Theme\Model\Data\Design\ConfigFactory;
use Magento\Theme\Model\Design\Config\Validator;
use Magento\Theme\Api\Data\DesignConfigInterface;

class DesignProcessorFacadeTest extends TestCase
{
    /**
     * @var Validator|MockObject
     */
    private $validator;

    /**
     * @var ConfigFactory|MockObject
     */
    private $configFactory;

    /**
     * @var DesignProcessorFacade
     */
    private $designProcessorFacade;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(Validator::class);
        $this->configFactory = $this->createMock(ConfigFactory::class);

        $this->designProcessorFacade = new DesignProcessorFacade(
            $this->validator,
            $this->configFactory
        );
    }

    /**
     * @return void
     * @throws Exception
     * @throws LocalizedException
     */
    public function testBeforeProcessWithLockTargetValidDesignPath()
    {
        $processorFacade = $this->createMock(ProcessorFacade::class);
        $path = 'design/theme/custom';
        $value = 'custom_value';
        $scope = 'default';
        $scopeCode = null;
        $lock = false;
        $lockTarget = 'app_env';

        $designConfig = $this->createMock(DesignConfigInterface::class);
        $this->configFactory->expects($this->once())
            ->method('create')
            ->with($scope, $scopeCode, ['theme_custom' => $value])
            ->willReturn($designConfig);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($designConfig);

        $result = $this->designProcessorFacade->beforeProcessWithLockTarget(
            $processorFacade,
            $path,
            $value,
            $scope,
            $scopeCode,
            $lock,
            $lockTarget
        );

        $this->assertEquals([$path, $value, $scope, $scopeCode, $lock, $lockTarget], $result);
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    public function testBeforeProcessWithLockTargetNonDesignPath()
    {
        $processorFacade = $this->createMock(ProcessorFacade::class);
        $path = 'non_design/path';
        $value = 'value';
        $scope = 'default';
        $scopeCode = null;
        $lock = false;
        $lockTarget = 'app_env';

        $this->configFactory->expects($this->never())
            ->method('create');

        $this->validator->expects($this->never())
            ->method('validate');

        $result = $this->designProcessorFacade->beforeProcessWithLockTarget(
            $processorFacade,
            $path,
            $value,
            $scope,
            $scopeCode,
            $lock,
            $lockTarget
        );

        $this->assertEquals([$path, $value, $scope, $scopeCode, $lock, $lockTarget], $result);
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    public function testBeforeProcessWithLockTargetThrowsException()
    {
        $this->expectException(LocalizedException::class);

        $processorFacade = $this->createMock(ProcessorFacade::class);
        $path = 'design/theme/custom';
        $value = 'custom_value';
        $scope = 'default';
        $scopeCode = null;
        $lock = false;
        $lockTarget = 'app_env';

        $designConfig = $this->createMock(DesignConfigInterface::class);
        $this->configFactory->expects($this->once())
            ->method('create')
            ->with($scope, $scopeCode, ['theme_custom' => $value])
            ->willReturn($designConfig);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($designConfig)
            ->willThrowException(new LocalizedException(__('Validation error')));

        $this->designProcessorFacade->beforeProcessWithLockTarget(
            $processorFacade,
            $path,
            $value,
            $scope,
            $scopeCode,
            $lock,
            $lockTarget
        );
    }
}
