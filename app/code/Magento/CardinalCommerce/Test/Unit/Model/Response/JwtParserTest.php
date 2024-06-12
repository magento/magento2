<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Test\Unit\Model\Response;

use Magento\CardinalCommerce\Model\Config;
use Magento\CardinalCommerce\Model\JwtManagement;
use Magento\CardinalCommerce\Model\Response\JwtParser;
use Magento\CardinalCommerce\Model\Response\JwtPayloadValidatorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JwtParserTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var JwtParser
     */
    private $model;

    /**
     * @var MockObject|Config
     */
    private $configMock;

    /**
     * @var MockObject|JwtManagement
     */
    private $jwtManagementMock;

    /**
     * @var MockObject|JwtPayloadValidatorInterface
     */
    private $jwtPayloadValidatorMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getApiKey', 'isDebugModeEnabled'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->jwtManagementMock = $this->getMockBuilder(JwtManagement::class)
            ->onlyMethods(['decode'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->jwtPayloadValidatorMock = $this->getMockBuilder(JwtPayloadValidatorInterface::class)
            ->onlyMethods(['validate'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = $this->objectManager->getObject(
            JwtParser::class,
            [
                'jwtManagement' => $this->jwtManagementMock,
                'config' => $this->configMock,
                'tokenValidator' => $this->jwtPayloadValidatorMock
            ]
        );

        $this->configMock->expects($this->any())
            ->method('getApiKey')
            ->willReturn('API Key');

        $this->configMock->expects($this->any())
            ->method('isDebugModeEnabled')
            ->willReturn(false);

        $this->jwtManagementMock->expects($this->any())
            ->method('decode')
            ->with('string_to_test', 'API Key')
            ->willReturn(['mockResult' => 'jwtPayload']);
    }

    /**
     * Tests Jwt Parser execute with the result and no exception.
     */
    public function testExecuteWithNoException()
    {
        /* Validate Success */
        $this->jwtPayloadValidatorMock->expects($this->any())
            ->method('validate')
            ->with(['mockResult' => 'jwtPayload'])
            ->willReturn(true);

        /* Assert the result of function */
        $jwtPayload = $this->model->execute('string_to_test');
        $this->assertEquals(
            ['mockResult' => 'jwtPayload'],
            $jwtPayload
        );
    }

    /**
     * Tests Jwt Parser execute with exception and no result.
     */
    public function testExecuteWithException()
    {
        /* Validate Fail */
        $this->jwtPayloadValidatorMock->expects($this->any())
            ->method('validate')
            ->with(['mockResult' => 'jwtPayload'])
            ->willReturn(false);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            'Authentication Failed. Your card issuer cannot authenticate this card. ' .
            'Please select another card or form of payment to complete your purchase.'
        );

        /* Execute function */
        $this->model->execute('string_to_test');
    }
}
