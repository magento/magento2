<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\Unit\Model\Customer\Plugin;

class AjaxLoginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Checkout\Model\Session
     */
    protected $sessionManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Captcha\Helper\Data
     */
    protected $captchaHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $captchaMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultJsonMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Customer\Controller\Ajax\Login
     */
    protected $loginControllerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializerMock;

    /**
     * @var array
     */
    protected $formIds;

    /**
     * @var \Magento\Captcha\Model\Customer\Plugin\AjaxLogin
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->sessionManagerMock = $this->createPartialMock(\Magento\Checkout\Model\Session::class, ['setUsername']);
        $this->captchaHelperMock = $this->createMock(\Magento\Captcha\Helper\Data::class);
        $this->captchaMock = $this->createMock(\Magento\Captcha\Model\DefaultModel::class);
        $this->jsonFactoryMock = $this->createPartialMock(
            \Magento\Framework\Controller\Result\JsonFactory::class,
            ['create']
        );
        $this->resultJsonMock = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->loginControllerMock = $this->createMock(\Magento\Customer\Controller\Ajax\Login::class);

        $this->loginControllerMock->expects($this->any())->method('getRequest')
            ->willReturn($this->requestMock);

        $this->captchaHelperMock
            ->expects($this->exactly(1))
            ->method('getCaptcha')
            ->willReturn($this->captchaMock);

        $this->formIds = ['user_login'];
        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\Serializer\Json::class);

        $this->model = new \Magento\Captcha\Model\Customer\Plugin\AjaxLogin(
            $this->captchaHelperMock,
            $this->sessionManagerMock,
            $this->jsonFactoryMock,
            $this->formIds,
            $this->serializerMock
        );
    }

    /**
     * Test aroundExecute.
     */
    public function testAroundExecute()
    {
        $username = 'name';
        $captchaString = 'string';
        $requestData = [
            'username' => $username,
            'captcha_string' => $captchaString,
            'captcha_form_id' => $this->formIds[0]
        ];
        $requestContent = json_encode($requestData);

        $this->requestMock->expects($this->once())->method('getContent')->willReturn($requestContent);
        $this->captchaMock->expects($this->once())->method('isRequired')->with($username)
            ->willReturn(true);
        $this->captchaMock->expects($this->once())->method('logAttempt')->with($username);
        $this->captchaMock->expects($this->once())->method('isCorrect')->with($captchaString)
            ->willReturn(true);
        $this->serializerMock->expects($this->once())->method('unserialize')->willReturn($requestData);

        $closure = function () {
            return 'result';
        };

        $this->captchaHelperMock
            ->expects($this->exactly(1))
            ->method('getCaptcha')
            ->with('user_login')
            ->willReturn($this->captchaMock);

        $this->assertEquals('result', $this->model->aroundExecute($this->loginControllerMock, $closure));
    }

    /**
     * Test aroundExecuteIncorrectCaptcha.
     */
    public function testAroundExecuteIncorrectCaptcha()
    {
        $username = 'name';
        $captchaString = 'string';
        $requestData = [
            'username' => $username,
            'captcha_string' => $captchaString,
            'captcha_form_id' => $this->formIds[0]
        ];
        $requestContent = json_encode($requestData);

        $this->requestMock->expects($this->once())->method('getContent')->willReturn($requestContent);
        $this->captchaMock->expects($this->once())->method('isRequired')->with($username)
            ->willReturn(true);
        $this->captchaMock->expects($this->once())->method('logAttempt')->with($username);
        $this->captchaMock->expects($this->once())->method('isCorrect')
            ->with($captchaString)->willReturn(false);
        $this->serializerMock->expects($this->once())->method('unserialize')->willReturn($requestData);

        $this->sessionManagerMock->expects($this->once())->method('setUsername')->with($username);
        $this->jsonFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->resultJsonMock);

        $this->resultJsonMock
            ->expects($this->once())
            ->method('setData')
            ->with(['errors' => true, 'message' => __('Incorrect CAPTCHA')])
            ->willReturnSelf();

        $closure = function () {
        };
        $this->assertEquals($this->resultJsonMock, $this->model->aroundExecute($this->loginControllerMock, $closure));
    }

    /**
     * @dataProvider aroundExecuteCaptchaIsNotRequired
     * @param string $username
     * @param array $requestContent
     */
    public function testAroundExecuteCaptchaIsNotRequired($username, $requestContent)
    {
        $this->requestMock->expects($this->once())->method('getContent')
            ->willReturn(json_encode($requestContent));
        $this->serializerMock->expects($this->once())->method('unserialize')
            ->willReturn($requestContent);

        $this->captchaMock->expects($this->once())->method('isRequired')->with($username)
            ->willReturn(false);
        $this->captchaMock->expects($this->never())->method('logAttempt')->with($username);
        $this->captchaMock->expects($this->never())->method('isCorrect');

        $closure = function () {
            return 'result';
        };
        $this->assertEquals('result', $this->model->aroundExecute($this->loginControllerMock, $closure));
    }

    /**
     * @return array
     */
    public function aroundExecuteCaptchaIsNotRequired(): array
    {
        return [
            [
                'username' => 'name',
                'requestData' => ['username' => 'name', 'captcha_string' => 'string'],
            ],
            [
                'username' => 'name',
                'requestData' =>
                    [
                        'username' => 'name',
                        'captcha_string' => 'string',
                        'captcha_form_id' => $this->formIds[0]
                    ],
            ],
            [
                'username' => null,
                'requestData' =>
                    [
                        'username' => null,
                        'captcha_string' => 'string',
                        'captcha_form_id' => $this->formIds[0]
                    ],
            ],
            [
                'username' => 'name',
                'requestData' =>
                    [
                        'username' => 'name',
                        'captcha_string' => 'string',
                        'captcha_form_id' => null
                    ],
            ],
        ];
    }
}
