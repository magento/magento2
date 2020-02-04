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
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

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
     * @var CaptchaStringResolver|PHPUnit_Framework_MockObject_MockObject
     */
    private $captchaStringResolverMock;

    /**
     * @var UserContextInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $currentUserMock;

    /**
     * @var CustomerRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var Data|PHPUnit_Framework_MockObject_MockObject
     */
    private $captchaHelperMock;

    /**
     * @var DefaultModel|PHPUnit_Framework_MockObject_MockObject
     */
    private $captchaMock;

    /**
     * @var RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * Set Up
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->captchaHelperMock = $this->createMock(Data::class);
        $this->captchaStringResolverMock = $this->createMock(CaptchaStringResolver::class);
        $this->currentUserMock = $this->getMockBuilder(UserContextInterface::class)
            ->getMockForAbstractClass();
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->captchaMock = $this->createMock(DefaultModel::class);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)->getMock();

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
            ->will($this->returnValue($this->captchaMock));
        $this->captchaMock->expects($this->once())->method('isRequired')
            ->will($this->returnValue($captchaIsRequired));

        if ($captchaIsRequired) {
            $this->captchaStringResolverMock->expects($this->once())->method('resolve')
                ->with($this->requestMock, static::FORM_ID)->will($this->returnValue($word));
            $this->captchaMock->expects($this->once())->method('isCorrect')->with($word)
                ->will($this->returnValue($captchaWordIsValid));
        }

        $this->model->validateSending($this->requestMock);
    }

    /**
     * Testing the wrong used word for captcha
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage  Incorrect CAPTCHA
     */
    public function testWrongCaptcha()
    {
        $word = 'test-word';
        $captchaIsRequired = true;
        $captchaWordIsCorrect = false;
        $this->captchaHelperMock->expects($this->once())->method('getCaptcha')->with(static::FORM_ID)
            ->will($this->returnValue($this->captchaMock));
        $this->captchaMock->expects($this->once())->method('isRequired')
            ->will($this->returnValue($captchaIsRequired));
        $this->captchaStringResolverMock->expects($this->any())->method('resolve')
            ->with($this->requestMock, static::FORM_ID)->will($this->returnValue($word));
        $this->captchaMock->expects($this->any())->method('isCorrect')->with($word)
            ->will($this->returnValue($captchaWordIsCorrect));

        $this->model->validateSending($this->requestMock);
    }

    /**
     * Providing captcha settings
     *
     * @return array
     */
    public function captchaProvider(): array
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
