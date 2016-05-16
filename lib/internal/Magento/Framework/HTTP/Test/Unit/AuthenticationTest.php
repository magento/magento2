<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\Test\Unit;

class AuthenticationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $server
     * @param string $expectedLogin
     * @param string $expectedPass
     * @dataProvider getCredentialsDataProvider
     */
    public function testGetCredentials($server, $expectedLogin, $expectedPass)
    {
        $request = $this->getMock('\Magento\Framework\App\Request\Http', [], [], '', false);
        $request->expects($this->once())->method('getServerValue')->will($this->returnValue($server));
        $response = $this->getMock('\Magento\Framework\App\Response\Http', [], [], '', false);
        $authentication = new \Magento\Framework\HTTP\Authentication($request, $response);
        $this->assertSame([$expectedLogin, $expectedPass], $authentication->getCredentials());
    }

    /**
     * @return array
     */
    public function getCredentialsDataProvider()
    {
        $login = 'login';
        $password = 'password';
        $header = 'Basic bG9naW46cGFzc3dvcmQ=';

        $anotherLogin = 'another_login';
        $anotherPassword = 'another_password';
        $anotherHeader = 'Basic YW5vdGhlcl9sb2dpbjphbm90aGVyX3Bhc3N3b3Jk';

        return [
            [[], '', ''],
            [['REDIRECT_HTTP_AUTHORIZATION' => $header], $login, $password],
            [['HTTP_AUTHORIZATION' => $header], $login, $password],
            [['Authorization' => $header], $login, $password],
            [
                [
                    'REDIRECT_HTTP_AUTHORIZATION' => $header,
                    'PHP_AUTH_USER' => $anotherLogin,
                    'PHP_AUTH_PW' => $anotherPassword,
                ],
                $anotherLogin,
                $anotherPassword
            ],
            [
                [
                    'REDIRECT_HTTP_AUTHORIZATION' => $header,
                    'PHP_AUTH_USER' => $anotherLogin,
                    'PHP_AUTH_PW' => $anotherPassword,
                ],
                $anotherLogin,
                $anotherPassword
            ],
            [
                ['REDIRECT_HTTP_AUTHORIZATION' => $header, 'HTTP_AUTHORIZATION' => $anotherHeader],
                $anotherLogin,
                $anotherPassword
            ]
        ];
    }

    public function testSetAuthenticationFailed()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $request = $objectManager->getObject('Magento\Framework\App\Request\Http');
        $response = $objectManager->getObject('Magento\Framework\App\Response\Http');

        $authentication = $objectManager->getObject(
            'Magento\Framework\HTTP\Authentication',
            [
                'httpRequest' => $request,
                'httpResponse' => $response
            ]
        );
        $realm = uniqid();
        $authentication->setAuthenticationFailed($realm);
        $headers = $response->getHeaders();

        $this->assertTrue($headers->has('WWW-Authenticate'));
        $header  = $headers->get('WWW-Authenticate');
        $this->assertEquals('Basic realm="' . $realm . '"', $header->current()->getFieldValue());
        $this->assertContains('401', $response->getBody());
    }
}
