<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Service;

use Magento\AdminAdobeIms\Service\ImsCommandOptionService;
use Magento\AdminAdobeIms\Service\ImsCommandValidationService;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImsCommandOptionServiceTest extends TestCase
{
    private const VALID_ORGANIZATION_ID = '12121212ABCD1211AA11ABCD';
    private const VALID_ORGANIZATION_ID_ALTERNATE = '12121212ABCD1211AA11ABCD@AdobeOrg';
    private const VALID_CLIENT_ID = 'AdobeCommerceIMS';
    private const VALID_CLIENT_SECRET = 'valid_client-secret';

    private const INVALID_ORGANIZATION_ID = '12121212AB$D1211AA11ABCD';
    private const INVALID_CLIENT_ID = '12121212$$ABCD1211AA11';
    private const INVALID_CLIENT_SECRET = '1212121$$$2ABCD1211AA11';

    /**
     * @var ImsCommandOptionService
     */
    private $imsCommandOptionService;

    /**
     * @var ImsCommandValidationService|MockObject
     */
    private $imsCommandValidationServiceMock;

    /**
     * @var InputInterface|MockObject
     */
    private $inputMock;

    /**
     * @var OutputInterface|MockObject
     */
    private $outputMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->imsCommandValidationServiceMock = $this->getMockBuilder(ImsCommandValidationService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();

        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();

        $this->imsCommandOptionService = $objectManagerHelper->getObject(
            ImsCommandOptionService::class,
            [
                'imsCommandValidationService' => $this->imsCommandValidationServiceMock
            ]
        );
    }

    /**
     * @dataProvider validInput
     * @param string $argument
     * @param string $value
     * @param string $validatorMethod
     * @return void
     * @throws LocalizedException
     */
    public function testValidInputWillBeReturned(string $argument, string $value, string $validatorMethod): void
    {
        $helperMock = $this->getMockBuilder(QuestionHelper::class)
            ->getMock();

        $this->inputMock
            ->method('getOption')
            ->with($argument)
            ->willReturn($value);

        $this->imsCommandValidationServiceMock
            ->method($validatorMethod)
            ->with($value)
            ->willReturn($value);

        $input = $this->executeGetOption($argument, $helperMock);

        $this->assertEquals(
            $value,
            $input
        );
    }

    /**
     * @dataProvider validInput
     * @param string $argument
     * @param string $value
     * @param string $validatorMethod
     * @return void
     * @throws LocalizedException
     */
    public function testOrganizationIdPromptReturnsOrgId(
        string $argument,
        string $value,
        string $validatorMethod
    ): void {
        $this->inputMock
            ->method('getOption')
            ->with($argument)
            ->willReturn('');

        $this->imsCommandValidationServiceMock
            ->method($validatorMethod)
            ->with($value)
            ->willReturn($value);

        $helperMock = $this->getMockBuilder(QuestionHelper::class)
            ->getMock();
        $helperMock->method('ask')
            ->willReturn($value)
        ;

        $input = $this->executeGetOption($argument, $helperMock);

        $this->assertEquals(
            $value,
            $input
        );
    }

    /**
     * @dataProvider validInput
     * @param string $argument
     * @param string $value
     * @param string $validatorMethod
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testEmptyOrganizationIdThrowsException(
        string $argument,
        string $value,
        string $validatorMethod
    ): void {
        $this->inputMock
            ->method('getOption')
            ->with($argument)
            ->willReturn('');

        $expectedExceptionMessage = __('This field is required to enable the Admin Adobe IMS Module');
        $expectedException = new LocalizedException($expectedExceptionMessage);

        $helperMock = $this->getMockBuilder(QuestionHelper::class)
            ->getMock();
        $helperMock->method('ask')
            ->willThrowException($expectedException)
        ;

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('This field is required to enable the Admin Adobe IMS Module');

        $this->executeGetOption($argument, $helperMock);
    }

    /**
     * @dataProvider invalidInput
     * @param $argument
     * @param $value
     * @param $validatorMethod
     * @param $exceptionMessage
     * @return void
     */
    public function testInvalidOrganizationIdThrowsException(
        $argument,
        $value,
        $validatorMethod,
        $exceptionMessage
    ): void {
        $this->inputMock
            ->method('getOption')
            ->with($argument)
            ->willReturn($value);

        $expectedExceptionMessage = __($exceptionMessage);
        $expectedException = new LocalizedException($expectedExceptionMessage);

        $helperMock = $this->getMockBuilder(QuestionHelper::class)
            ->getMock();

        $this->imsCommandValidationServiceMock
            ->method($validatorMethod)
            ->with($value)
            ->willThrowException($expectedException);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->executeGetOption($argument, $helperMock);
    }

    /**
     * @param $argument
     * @param $helperMock
     * @return string|null
     * @throws LocalizedException
     */
    public function executeGetOption($argument, $helperMock): ?string
    {
        $input = null;
        switch ($argument) {
            case 'organization-id':
                $input = $this->imsCommandOptionService->getOrganizationId(
                    $this->inputMock,
                    $this->outputMock,
                    $helperMock,
                    $argument
                );
                break;
            case 'client-id':
                $input = $this->imsCommandOptionService->getClientId(
                    $this->inputMock,
                    $this->outputMock,
                    $helperMock,
                    $argument
                );
                break;
            case 'client-secret':
                $input = $this->imsCommandOptionService->getClientSecret(
                    $this->inputMock,
                    $this->outputMock,
                    $helperMock,
                    $argument
                );
                break;
        }

        return $input;
    }

    /**
     * Data provider for valid CLI Input
     * - option name
     * - option value
     * - validator method
     *
     * @return string[][]
     */
    public function validInput(): array
    {
        return [
            [
                'organization-id',
                self::VALID_ORGANIZATION_ID,
                'organizationIdValidator'
            ],
            [
                'organization-id',
                self::VALID_ORGANIZATION_ID_ALTERNATE,
                'organizationIdValidator'
            ],
            [
                'client-id',
                self::VALID_CLIENT_ID,
                'clientIdValidator'
            ],
            [
                'client-secret',
                self::VALID_CLIENT_SECRET,
                'clientSecretValidator'
            ]
        ];
    }

    /**
     * Data provider for valid CLI Input
     * - option name
     * - option value
     * - validator method
     * - exception message
     *
     * @return string[][]
     */
    public function invalidInput(): array
    {
        return [
            [
                'organization-id',
                self::INVALID_ORGANIZATION_ID,
                'organizationIdValidator',
                'No valid Organization ID provided'
            ],
            [
                'client-id',
                self::INVALID_CLIENT_ID,
                'clientIdValidator',
                'No valid Client ID provided'
            ],
            [
                'client-secret',
                self::INVALID_CLIENT_SECRET,
                'clientSecretValidator',
                'No valid Client Secret provided'
            ]
        ];
    }
}
