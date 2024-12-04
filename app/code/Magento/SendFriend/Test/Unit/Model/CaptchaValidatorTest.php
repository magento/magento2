<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SendFriend\Test\Unit\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Captcha\Helper\Data;
use Magento\Captcha\Model\DefaultModel;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SendFriend\Model\CaptchaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test CaptchaValidatorTest
 */
class CaptchaValidatorTest extends TestCase
{
    const FORM_ID = 'product_sendtofriend_form';

    /**
     * @var CaptchaValidator
     */
    private $model;

    /**
     * @var CaptchaStringResolver|MockObject
     */
    private $captchaStringResolverMock;

    /**
     * @var UserContextInterface|MockObject
     */
    private $currentUserMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var Data|MockObject
     */
    private $captchaHelperMock;

    /**
     * @var DefaultModel|MockObject
     */
    private $captchaMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->captchaHelperMock = $this->createMock(Data::class);
        $this->captchaStringResolverMock = $this->createMock(CaptchaStringResolver::class);
        $this->currentUserMock = $this->getMockBuilder(UserContextInterface::class)
            ->getMockForAbstractClass();
        $this->customerRepositoryMock = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->captchaMock = $this->createMock(DefaultModel::class);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMock();

        $this->model = $objectManager->getObject(
            CaptchaValidator::class,
            [
                'captchaHelper' => $this->captchaHelperMock,
                'captchaStringResolver' => $this->captchaStringResolverMock,
                'currentUser' => $this->currentUserMock,
                'customerRepository' => $this->customerRepositoryMock,
            ]
        );
    }

    /**
     * Testing the captcha validation before sending the email
     *
     * @dataProvider captchaProvider
     *
     * @param bool $captchaIsRequired
     * @param bool $captchaWordIsValid
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testCaptchaValidationOnSend(bool $captchaIsRequired, bool $captchaWordIsValid)
    {
        $word = 'test-word';
        $this->captchaHelperMock->expects($this->once())->method('getCaptcha')->with(static::FORM_ID)
            ->willReturn($this->captchaMock);
        $this->captchaMock->expects($this->once())->method('isRequired')
            ->willReturn($captchaIsRequired);

        if ($captchaIsRequired) {
            $this->captchaStringResolverMock->expects($this->once())->method('resolve')
                ->with($this->requestMock, static::FORM_ID)->willReturn($word);
            $this->captchaMock->expects($this->once())->method('isCorrect')->with($word)
                ->willReturn($captchaWordIsValid);
        }

        $this->model->validateSending($this->requestMock);
    }

    /**
     * Testing the wrong used word for captcha
     */
    public function testWrongCaptcha()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Incorrect CAPTCHA');
        $word = 'test-word';
        $captchaIsRequired = true;
        $captchaWordIsCorrect = false;
        $this->captchaHelperMock->expects($this->once())->method('getCaptcha')->with(static::FORM_ID)
            ->willReturn($this->captchaMock);
        $this->captchaMock->expects($this->once())->method('isRequired')
            ->willReturn($captchaIsRequired);
        $this->captchaStringResolverMock->expects($this->any())->method('resolve')
            ->with($this->requestMock, static::FORM_ID)->willReturn($word);
        $this->captchaMock->expects($this->any())->method('isCorrect')->with($word)
            ->willReturn($captchaWordIsCorrect);

        $this->model->validateSending($this->requestMock);
    }

    /**
     * Providing captcha settings
     *
     * @return array
     */
    public static function captchaProvider(): array
    {
        return [
            [
                true,
                true
            ], [
                false,
                false
            ]
        ];
    }
}
