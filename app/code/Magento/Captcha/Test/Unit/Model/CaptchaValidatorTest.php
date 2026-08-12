<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Model;

use Magento\Captcha\Helper\Data;
use Magento\Captcha\Model\CaptchaValidator;
use Magento\Captcha\Model\DefaultModel;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InputException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test CaptchaValidator
 */
class CaptchaValidatorTest extends TestCase
{
    private const FORM_ID = 'user_create';
    private const CAPTCHA_WORD = 'test-word';

    /**
     * @var CaptchaValidator
     */
    private $model;

    /**
     * @var Data|MockObject
     */
    private $captchaHelperMock;

    /**
     * @var CaptchaStringResolver|MockObject
     */
    private $captchaResolverMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var DefaultModel|MockObject
     */
    private $captchaMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->captchaHelperMock = $this->createMock(Data::class);
        $this->captchaResolverMock = $this->createMock(CaptchaStringResolver::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->captchaMock = $this->createMock(DefaultModel::class);

        $this->model = new CaptchaValidator(
            $this->captchaHelperMock,
            $this->captchaResolverMock,
            $this->requestMock
        );
    }

    /**
     * Test validate with captcha required and correct word
     *
     * @return void
     * @throws InputException
     */
    public function testValidateWithCaptchaRequiredAndCorrectWord(): void
    {
        $this->setupCaptchaMocks(true, true, self::CAPTCHA_WORD);

        $this->model->validate(self::FORM_ID);
    }

    /**
     * Test validate with captcha required and incorrect word
     *
     * @return void
     */
    public function testValidateWithCaptchaRequiredAndIncorrectWord(): void
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Incorrect CAPTCHA');

        $this->setupCaptchaMocks(true, false, self::CAPTCHA_WORD);

        $this->model->validate(self::FORM_ID);
    }

    /**
     * Test validate with captcha not required
     *
     * @return void
     * @throws InputException
     */
    public function testValidateWithCaptchaNotRequired(): void
    {
        $this->captchaHelperMock->expects($this->once())
            ->method('getCaptcha')
            ->with(self::FORM_ID)
            ->willReturn($this->captchaMock);

        $this->captchaMock->expects($this->once())
            ->method('isRequired')
            ->with(self::FORM_ID)
            ->willReturn(false);

        $this->captchaResolverMock->expects($this->never())
            ->method('resolve');

        $this->model->validate(self::FORM_ID);
    }

    /**
     * Test validate with empty captcha value throws exception
     *
     * @return void
     */
    public function testValidateWithEmptyCaptchaValue(): void
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('CAPTCHA is required.');

        $this->captchaHelperMock->expects($this->once())
            ->method('getCaptcha')
            ->with(self::FORM_ID)
            ->willReturn($this->captchaMock);

        $this->captchaMock->expects($this->once())
            ->method('isRequired')
            ->with(self::FORM_ID)
            ->willReturn(true);

        $this->captchaResolverMock->expects($this->once())
            ->method('resolve')
            ->with($this->requestMock, self::FORM_ID)
            ->willReturn(''); // Empty value

        $this->captchaMock->expects($this->never())
            ->method('isCorrect');

        $this->model->validate(self::FORM_ID);
    }

    /**
     * Test validate with null captcha value throws exception
     *
     * @return void
     */
    public function testValidateWithNullCaptchaValue(): void
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('CAPTCHA is required.');

        $this->captchaHelperMock->expects($this->once())
            ->method('getCaptcha')
            ->with(self::FORM_ID)
            ->willReturn($this->captchaMock);

        $this->captchaMock->expects($this->once())
            ->method('isRequired')
            ->with(self::FORM_ID)
            ->willReturn(true);

        $this->captchaResolverMock->expects($this->once())
            ->method('resolve')
            ->with($this->requestMock, self::FORM_ID)
            ->willReturn(null);

        $this->captchaMock->expects($this->never())
            ->method('isCorrect');

        $this->model->validate(self::FORM_ID);
    }

    /**
     * Test validate returns early when captcha not required
     *
     * @return void
     * @throws InputException
     */
    public function testValidateReturnsEarlyWhenNotRequired(): void
    {
        $this->captchaHelperMock->expects($this->once())
            ->method('getCaptcha')
            ->with(self::FORM_ID)
            ->willReturn($this->captchaMock);

        $this->captchaMock->expects($this->once())
            ->method('isRequired')
            ->with(self::FORM_ID)
            ->willReturn(false);

        // These should not be called when captcha is not required
        $this->captchaResolverMock->expects($this->never())
            ->method('resolve');
        $this->captchaMock->expects($this->never())
            ->method('isCorrect');

        $this->model->validate(self::FORM_ID);
    }

    /**
     * Setup captcha mocks
     *
     * @param bool $isRequired
     * @param bool $isCorrect
     * @param string|null $captchaValue
     * @return void
     */
    private function setupCaptchaMocks(bool $isRequired, bool $isCorrect, ?string $captchaValue): void
    {
        $this->captchaHelperMock->expects($this->once())
            ->method('getCaptcha')
            ->with(self::FORM_ID)
            ->willReturn($this->captchaMock);

        $this->captchaMock->expects($this->once())
            ->method('isRequired')
            ->with(self::FORM_ID)
            ->willReturn($isRequired);

        if ($isRequired && $captchaValue) {
            $this->captchaResolverMock->expects($this->once())
                ->method('resolve')
                ->with($this->requestMock, self::FORM_ID)
                ->willReturn($captchaValue);

            $this->captchaMock->expects($this->once())
                ->method('isCorrect')
                ->with($captchaValue)
                ->willReturn($isCorrect);
        }
    }
}
