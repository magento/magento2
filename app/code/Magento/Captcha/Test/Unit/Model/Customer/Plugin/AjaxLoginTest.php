<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Model\Customer\Plugin;

class AjaxLoginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $captchaHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loginControllerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
        $this->sessionManagerMock = $this->getMock(
            \Magento\Checkout\Model\Session::class,
            ['setUsername'],
            [],
            '',
            false
        );
        $this->captchaHelperMock = $this->getMock(\Magento\Captcha\Helper\Data::class, [], [], '', false);
        $this->captchaMock = $this->getMock(\Magento\Captcha\Model\DefaultModel::class, [], [], '', false);
        $this->jsonFactoryMock = $this->getMock(
            \Magento\Framework\Controller\Result\JsonFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resultJsonMock = $this->getMock(\Magento\Framework\Controller\Result\Json::class, [], [], '', false);
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->loginControllerMock = $this->getMock(\Magento\Customer\Controller\Ajax\Login::class, [], [], '', false);

        $this->loginControllerMock->expects($this->any())->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->captchaHelperMock->expects($this->once())->method('getCaptcha')
            ->with('user_login')->will($this->returnValue($this->captchaMock));
        $this->formIds = ['user_login'];
        $this->serializerMock = $this->getMock(
            \Magento\Framework\Serialize\SerializerInterface::class,
            [],
            [],
            '',
            false
        );

        $this->model = new \Magento\Captcha\Model\Customer\Plugin\AjaxLogin(
            $this->captchaHelperMock,
            $this->sessionManagerMock,
            $this->jsonFactoryMock,
            $this->serializerMock,
            $this->formIds
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
        $this->serializerMock->expects(($this->once()))->method('unserialize')->will($this->returnValue($requestData));

        $closure = function () {
            return 'result';
        };
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
        $this->serializerMock->expects(($this->once()))->method('unserialize')->will($this->returnValue($requestData));

        $this->sessionManagerMock->expects($this->once())->method('setUsername')->with($username);
        $this->jsonFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->resultJsonMock));

        $this->resultJsonMock->expects($this->once())->method('setData')
            ->with(['errors' => true, 'message' => __('Incorrect CAPTCHA')])->will($this->returnValue('response'));

        $closure = function () {
        };
        $this->assertEquals('response', $this->model->aroundExecute($this->loginControllerMock, $closure));
    }

    /**
     * @dataProvider aroundExecuteCaptchaIsNotRequired
     * @param string $username
     * @param array $requestContent
     */
    public function testAroundExecuteCaptchaIsNotRequired($username, $requestContent)
    {
        $this->requestMock->expects($this->once())->method('getContent')->will($this->returnValue(json_encode($requestContent)));
        $this->serializerMock->expects(($this->once()))->method('unserialize')->will($this->returnValue($requestContent));

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
    public function aroundExecuteCaptchaIsNotRequired()
    {
        return [
            [
                'username' => 'name',
                'requestData' => ['username' => 'name', 'captcha_string' => 'string'],
            ],
            [
                'username' => null,
                'requestData' => ['captcha_string' => 'string'],
            ],
        ];
    }
}
