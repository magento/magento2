<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Validator\EntityArrayValidator;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Webapi\Request;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Webapi\Validator\EntityArrayValidator\InputArraySizeLimitValue;

/**
 * Verifies behavior of the input array size value of limitation
 */
class InputArraySizeLimitValueTest extends TestCase
{
    /**
     * @var Request|MockObject
     */
    private $requestMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var InputArraySizeLimitValue
     */
    private InputArraySizeLimitValue $inputArraySizeLimitValue;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Request::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->inputArraySizeLimitValue = new InputArraySizeLimitValue(
            $this->requestMock,
            $this->deploymentConfigMock
        );
    }

    /**
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function testIfValueNotNull()
    {
        $this->requestMock->expects(self::never())
            ->method('getPathInfo');
        $this->deploymentConfigMock->expects(self::never())
            ->method('get');
        $this->inputArraySizeLimitValue->set(3);
        $this->assertEquals(3, $this->inputArraySizeLimitValue->get());
    }

    /**
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function testIfValueNullAndRequestIsAsync()
    {
        $this->requestMock->expects(self::once())
            ->method('getPathInfo')
            ->willReturn('/async/V1/path');
        $this->deploymentConfigMock->expects(self::once())
            ->method('get')
            ->willReturn(40);
        $this->assertEquals(40, $this->inputArraySizeLimitValue->get());
    }

    /**
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function testIfValueNullAndRequestIsSync()
    {
        $this->requestMock->expects(self::once())
            ->method('getPathInfo')
            ->willReturn('/V1/path');
        $this->deploymentConfigMock->expects(self::once())
            ->method('get')
            ->willReturn(50);
        $this->assertEquals(50, $this->inputArraySizeLimitValue->get());
    }
}
