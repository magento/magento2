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

namespace Magento\Core\Controller\Request;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\App\RequestInterface */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_routerListMock;

    protected function setUp()
    {
        $this->_routerListMock = $this->getMock('\Magento\App\Route\ConfigInterface');
        $infoProcessorMock = $this->getMock('Magento\App\Request\PathInfoProcessorInterface');
        $infoProcessorMock->expects($this->any())->method('process')->will($this->returnArgument(1));
        $this->_model = new \Magento\App\Request\Http($this->_routerListMock, $infoProcessorMock);
    }

    /**
     * @param $serverVariables array
     * @param $expectedResult string
     * @dataProvider serverVariablesProvider
     */
    public function testGetDistroBaseUrl($serverVariables, $expectedResult)
    {
        $originalServerValue = $_SERVER;
        $_SERVER = $serverVariables;

        $this->assertEquals($expectedResult, $this->_model->getDistroBaseUrl());

        $_SERVER = $originalServerValue;
    }

    public function serverVariablesProvider()
    {
        $returnValue = array();
        $defaultServerData = array(
            'SCRIPT_NAME' => 'index.php',
            'HTTP_HOST' => 'sample.host.com',
            'SERVER_PORT' => '80',
            'HTTPS' => '1',
        );

        $secureUnusualPort = $noHttpsData = $httpsOffData = $noHostData = $noScriptNameData = $defaultServerData;

        unset($noScriptNameData['SCRIPT_NAME']);
        $returnValue['no SCRIPT_NAME'] = array($noScriptNameData, 'http://localhost/');

        unset($noHostData['HTTP_HOST']);
        $returnValue['no HTTP_HOST'] = array($noHostData, 'http://localhost/');

        $httpsOffData['HTTPS'] = 'off';
        $returnValue['HTTPS off'] = array($httpsOffData, 'http://sample.host.com/');

        unset($noHttpsData['HTTPS']);
        $returnValue['no HTTPS'] = array($noHttpsData, 'http://sample.host.com/');

        $noHttpsNoServerPort = $noHttpsData;
        unset($noHttpsNoServerPort['SERVER_PORT']);
        $returnValue['no SERVER_PORT'] = array($noHttpsNoServerPort, 'http://sample.host.com/');

        $noHttpsButSecurePort = $noHttpsData;
        $noHttpsButSecurePort['SERVER_PORT'] = 443;
        $returnValue['no HTTP but secure port'] = array($noHttpsButSecurePort, 'https://sample.host.com/');

        $notSecurePort = $noHttpsData;
        $notSecurePort['SERVER_PORT'] = 81;
        $notSecurePort['HTTP_HOST'] = 'sample.host.com:81';
        $returnValue['not secure not standard port'] = array($notSecurePort, 'http://sample.host.com:81/');

        $secureUnusualPort['SERVER_PORT'] = 441;
        $secureUnusualPort['HTTP_HOST'] = 'sample.host.com:441';
        $returnValue['not standard secure port'] = array($secureUnusualPort, 'https://sample.host.com:441/');

        $customUrlPathData = $noHttpsData;
        $customUrlPathData['SCRIPT_FILENAME'] = '/some/dir/custom.php';
        $returnValue['custom path'] = array($customUrlPathData, 'http://sample.host.com/');

        return $returnValue;
    }
}
