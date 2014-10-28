<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\HTTP;

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
        $request = $this->getMock('\Magento\Framework\App\Request\Http', array(), array(), '', false);
        $request->expects($this->once())->method('getServer')->will($this->returnValue($server));
        $response = $this->getMock('\Magento\Framework\App\Response\Http', array(), array(), '', false);
        $authentication = new \Magento\Framework\HTTP\Authentication($request, $response);
        $this->assertSame(array($expectedLogin, $expectedPass), $authentication->getCredentials());
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

        return array(
            array(array(), '', ''),
            array(array('REDIRECT_HTTP_AUTHORIZATION' => $header), $login, $password),
            array(array('HTTP_AUTHORIZATION' => $header), $login, $password),
            array(array('Authorization' => $header), $login, $password),
            array(
                array(
                    'REDIRECT_HTTP_AUTHORIZATION' => $header,
                    'PHP_AUTH_USER' => $anotherLogin,
                    'PHP_AUTH_PW' => $anotherPassword
                ),
                $anotherLogin,
                $anotherPassword
            ),
            array(
                array(
                    'REDIRECT_HTTP_AUTHORIZATION' => $header,
                    'PHP_AUTH_USER' => $anotherLogin,
                    'PHP_AUTH_PW' => $anotherPassword
                ),
                $anotherLogin,
                $anotherPassword
            ),
            array(
                array('REDIRECT_HTTP_AUTHORIZATION' => $header, 'HTTP_AUTHORIZATION' => $anotherHeader),
                $anotherLogin,
                $anotherPassword
            )
        );
    }

    public function testSetAuthenticationFailed()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
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
        $response->headersSentThrowsException = false;
        $authentication->setAuthenticationFailed($realm);
        $headers = $response->getHeaders();
        $this->assertArrayHasKey(0, $headers);
        $this->assertEquals('401 Unauthorized', $headers[0]['value']);
        $this->assertArrayHasKey(1, $headers);
        $this->assertContains('realm="' . $realm . '"', $headers[1]['value']);
        $this->assertContains('401', $response->getBody());
    }
}
