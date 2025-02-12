<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Rest;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\RequestMethodValidator;
use PHPUnit\Framework\TestCase;

class RequestMethodValidatorTest extends TestCase
{
    /**
     * @var RequestMethodValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new RequestMethodValidator();
    }

    public function testValidate(): void
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock->expects(self::once())
            ->method('getHttpMethod')
            ->willReturn('POST');
        $this->validator->validate($requestMock);
    }

    public function testValidateWithException(): void
    {
        $this->expectException(WebapiException::class);
        $this->expectExceptionMessage('The OPTIONS HTTP method is not supported.');

        $exceptionMock = $this->createMock(InputException::class);
        $requestMock = $this->createMock(Request::class);
        $requestMock->expects(self::once())
            ->method('getHttpMethod')
            ->willThrowException($exceptionMock);
        $requestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('OPTIONS');
        $this->validator->validate($requestMock);
    }
}
