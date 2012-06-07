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
 * @category    Mage
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Helper_HttpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Helper_Http
     */
    protected $_object = null;

    protected function setUp()
    {
        $this->_object = new Mage_Core_Helper_Http;
    }

    /**
     * @param array $server
     * @param string $expectedLogin
     * @param string $expectedPass
     * @dataProvider getHttpAuthCredentialsDataProvider
     */
    public function testGetHttpAuthCredentials($server, $expectedLogin, $expectedPass)
    {
        $request = $this->getMock('Zend_Controller_Request_Http', array('getServer'));
        $request->expects($this->once())->method('getServer')->will($this->returnValue($server));
        $this->assertSame(array($expectedLogin, $expectedPass), $this->_object->getHttpAuthCredentials($request));
    }

    /**
     * @return array
     */
    public function getHttpAuthCredentialsDataProvider()
    {
        $login    = 'login';
        $password = 'password';
        $header   = 'Basic bG9naW46cGFzc3dvcmQ=';

        $anotherLogin    = 'another_login';
        $anotherPassword = 'another_password';
        $anotherHeader   = 'Basic YW5vdGhlcl9sb2dpbjphbm90aGVyX3Bhc3N3b3Jk';

        return array(
            array(array(), '', ''),
            array(array('REDIRECT_HTTP_AUTHORIZATION' => $header), $login, $password),
            array(array('HTTP_AUTHORIZATION' => $header), $login, $password),
            array(array('Authorization' => $header), $login, $password),
            array(array(
                    'REDIRECT_HTTP_AUTHORIZATION' => $header,
                    'PHP_AUTH_USER' => $anotherLogin,
                    'PHP_AUTH_PW' => $anotherPassword
                ), $anotherLogin, $anotherPassword
            ),
            array(array(
                    'REDIRECT_HTTP_AUTHORIZATION' => $header,
                    'PHP_AUTH_USER' => $anotherLogin,
                    'PHP_AUTH_PW' => $anotherPassword
                ), $anotherLogin, $anotherPassword
            ),
            array(
                array('REDIRECT_HTTP_AUTHORIZATION' => $header, 'HTTP_AUTHORIZATION' => $anotherHeader,),
                $anotherLogin, $anotherPassword
            ),
        );
    }

    public function testFailHttpAuthentication()
    {
        $response = new Zend_Controller_Response_Http;
        $realm = uniqid();
        $response->headersSentThrowsException = false;
        $this->_object->failHttpAuthentication($response, $realm);
        $headers = $response->getHeaders();
        $this->assertArrayHasKey(0, $headers);
        $this->assertEquals('401 Unauthorized', $headers[0]['value']);
        $this->assertArrayHasKey(1, $headers);
        $this->assertContains('realm="' . $realm .'"', $headers[1]['value']);
        $this->assertContains('401', $response->getBody());
    }
}
