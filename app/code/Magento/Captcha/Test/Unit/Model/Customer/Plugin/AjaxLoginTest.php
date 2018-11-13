<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\Unit\Model\Customer\Plugin;

class AjaxLoginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Checkout\Model\Session
     */
    protected $sessionManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Captcha\Helper\Data
     */
    protected $captchaHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $captchaMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Controller\Ajax\Login
     */
    protected $loginControllerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Serialize\Serializer\Json
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

    protected function setUp()
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
            ->will($this->returnValue($this->requestMock));

        $this->captchaHelperMock
            ->expects($this->exactly(1))
            ->method('getCaptcha')
            ->will($this->returnValue($this->captchaMock));

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

        $this->requestMock->expects($this->once())->method('getContent')->will($this->returnValue($requestContent));
        $this->captchaMock->expects($this->once())->method('isRequired')->with($username)
            ->will($this->returnValue(true));
        $this->captchaMock->expects($this->once())->method('logAttempt')->with($username);
        $this->captchaMock->expects($this->once())->method('isCorrect')->with($captchaString)
            ->will($this->returnValue(true));
        $this->serializerMock->expects($this->once())->method('unserialize')->will($this->returnValue($requestData));

        $closure = function () {
            return 'result';
        };

        $this->captchaHelperMock
            ->expects($this->exactly(1))
            ->method('getCaptcha')
            ->with('user_login')
            ->will($this->returnValue($this->captchaMock));

        $this->assertEquals('result', $this->model->aroundExecute($this->loginControllerMock, $closure));
    }

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

        $this->requestMock->expects($this->once())->method('getContent')->will($this->returnValue($requestContent));
        $this->captchaMock->expects($this->once())->method('isRequired')->with($username)
            ->will($this->returnValue(true));
        $this->captchaMock->expects($this->once())->method('logAttempt')->with($username);
        $this->captchaMock->expects($this->once())->method('isCorrect')
            ->with($captchaString)->will($this->returnValue(false));
        $this->serializerMock->expects($this->once())->method('unserialize')->will($this->returnValue($requestData));

        $this->sessionManagerMock->expects($this->once())->method('setUsername')->with($username);
        $this->jsonFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->resultJsonMock));

        $this->resultJsonMock
            ->expects($this->once())
            ->method('setData')
            ->with(['errors' => true, 'message' => __('Incorrect CAPTCHA'), 'captcha' => true])
            ->will($this->returnSelf());

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
            ->will($this->returnValue(json_encode($requestContent)));
        $this->serializerMock->expects($this->once())->method('unserialize')
            ->will($this->returnValue($requestContent));

        $this->captchaMock->expects($this->once())->method('isRequired')->with($username)
            ->will($this->returnValue(false));
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
