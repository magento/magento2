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
 * @package     Mage_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Controller_Varien_Router_StandardTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test area code
     */
    const AREA_CODE = 'frontend';

    /**
     * Test VDE front name prefix
     */
    const VDE_FRONT_NAME = 'test_front_name';

    /**
     * Test VDE configuration data
     */
    const VDE_CONFIGURATION_DATA = 'vde_config_data';

    /**#@+
     * Test path and host
     */
    const TEST_PATH = '/customer/account';
    const TEST_HOST = 'http://test.domain';
    /**#@-*/

    /**
     * @var Mage_DesignEditor_Controller_Varien_Router_Standard
     */
    protected $_model;

    public function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param bool $isVde
     * @param bool $isLoggedIn
     * @param bool $isConfiguration
     * @param array $routers
     * @param string|null $matchedValue
     *
     * @dataProvider matchDataProvider
     */
    public function testMatch(
        Mage_Core_Controller_Request_Http $request,
        $isVde,
        $isLoggedIn,
        $isConfiguration,
        array $routers = array(),
        $matchedValue = null
    ) {
        $this->_model = $this->_prepareMocksForTestMatch($request, $isVde, $isLoggedIn, $isConfiguration, $routers);

        $this->assertEquals($matchedValue, $this->_model->match($request));
        if ($isVde && $isLoggedIn) {
            $this->assertEquals(self::TEST_PATH, $request->getPathInfo());
        }
    }

    /**
     * Data provider for testMatch
     *
     * @return array
     */
    public function matchDataProvider()
    {
        $vdeUrl    = self::TEST_HOST . '/' . self::VDE_FRONT_NAME . self::TEST_PATH;
        $notVdeUrl = self::TEST_HOST . self::TEST_PATH;

        $silencedMethods = array('_canBeStoreCodeInUrl');
        $excludedRouters = array(
            'admin' => 'admin router',
            'vde'   => 'vde router',
        );

        // test data to verify routers match logic
        $matchedRequest = $this->getMock('Mage_Core_Controller_Request_Http', $silencedMethods, array($vdeUrl));
        $routerMockedMethods = array('match');

        $matchedController = $this->getMockForAbstractClass('Mage_Core_Controller_Varien_ActionAbstract', array(), '',
            false
        );

        // method "match" will be invoked for this router because it's first in the list
        $matchedRouter = $this->getMock(
            'Mage_Core_Controller_Varien_Router_Base', $routerMockedMethods, array(), '', false
        );
        $matchedRouter->expects($this->once())
            ->method('match')
            ->with($matchedRequest)
            ->will($this->returnValue($matchedController));

        // method "match" will not be invoked for this router because controller will be found by first router
        $notMatchedRouter = $this->getMock(
            'Mage_Core_Controller_Varien_Router_Base', $routerMockedMethods, array(), '', false
        );
        $notMatchedRouter->expects($this->never())
            ->method('match');

        $matchedRouters = array_merge($excludedRouters,
            array('matched' => $matchedRouter, 'not_matched' => $notMatchedRouter)
        );

        return array(
            'not vde request' => array(
                '$request' => $this->getMock(
                    'Mage_Core_Controller_Request_Http', $silencedMethods, array($notVdeUrl)
                ),
                '$isVde'           => false,
                '$isLoggedIn'      => true,
                '$isConfiguration' => false,
            ),
            'not logged as admin' => array(
                '$request' => $this->getMock(
                    'Mage_Core_Controller_Request_Http', $silencedMethods, array($vdeUrl)
                ),
                '$isVde'           => true,
                '$isLoggedIn'      => false,
                '$isConfiguration' => false,
            ),
            'no matched routers' => array(
                '$request' => $this->getMock(
                    'Mage_Core_Controller_Request_Http', $silencedMethods, array($vdeUrl)
                ),
                '$isVde'           => true,
                '$isLoggedIn'      => true,
                '$isConfiguration' => false,
                '$routers'         => $excludedRouters
            ),
            'matched routers' => array(
                '$request'         => $matchedRequest,
                '$isVde'           => true,
                '$isLoggedIn'      => true,
                '$isConfiguration' => true,
                '$routers'         => $matchedRouters,
                '$matchedValue'    => $matchedController,
            ),
        );
    }

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param bool $isVde
     * @param bool $isLoggedIn
     * @param bool $isConfiguration
     * @param array $routers
     * @return Mage_DesignEditor_Controller_Varien_Router_Standard
     */
    protected function _prepareMocksForTestMatch(
        Mage_Core_Controller_Request_Http $request,
        $isVde,
        $isLoggedIn,
        $isConfiguration,
        array $routers
    ) {
        // default mocks - not affected on method functionality
        $controllerFactory  = $this->getMock('Mage_Core_Controller_Varien_Action_Factory', array(), array(), '', false);
        $filesystem         = $this->getMockBuilder('Magento_Filesystem')->disableOriginalConstructor()->getMock();
        $app                = $this->getMock('Mage_Core_Model_App', array(), array(), '', false);
        $testArea           = 'frontend';
        $testBaseController = 'Mage_Core_Controller_Varien_Action';

        $helper = $this->getMock('Mage_DesignEditor_Helper_Data', array('getFrontName'), array(), '', false);
        $helper->expects($this->atLeastOnce())
            ->method('getFrontName')
            ->will($this->returnValue(self::VDE_FRONT_NAME));

        $backendSession = $this->getMock('Mage_Backend_Model_Auth_Session', array('isLoggedIn'), array(), '', false);
        $backendSession->expects($isVde ? $this->once() : $this->never())
            ->method('isLoggedIn')
            ->will($this->returnValue($isLoggedIn));

        $frontController = $this->getMock('Mage_Core_Controller_Varien_Front',
            array('applyRewrites', 'getRouters'), array(), '', false
        );
        if ($isVde && $isLoggedIn) {
            $frontController->expects($this->once())
                ->method('applyRewrites')
                ->with($request);
            $frontController->expects($this->once())
                ->method('getRouters')
                ->will($this->returnValue($routers));
        }

        $stateModel = $this->getMock('Mage_DesignEditor_Model_State', array('update'), array(), '', false);
        if (array_key_exists('matched', $routers)) {
            $stateModel->expects($this->once())
                ->method('update')
                ->with(self::AREA_CODE);
        }

        $configuration = $this->getMock('Mage_Core_Model_Config', array('getNode'), array(), '', false);
        if ($isVde && $isLoggedIn) {
            $configurationData = null;
            if ($isConfiguration) {
                $configurationData = self::VDE_CONFIGURATION_DATA;
            }
            $configuration->expects($this->at(0))
                ->method('getNode')
                ->with(Mage_DesignEditor_Model_Area::AREA_VDE)
                ->will($this->returnValue($configurationData));

            if ($isConfiguration) {
                $elementMock = $this->getMock('stdClass', array('extend'), array(), '', false);
                $elementMock->expects($this->once())
                    ->method('extend')
                    ->with(self::VDE_CONFIGURATION_DATA, true);

                $configuration->expects($this->at(1))
                    ->method('getNode')
                    ->with(Mage_Core_Model_App_Area::AREA_FRONTEND)
                    ->will($this->returnValue($elementMock));
            }
        }

        $router = new Mage_DesignEditor_Controller_Varien_Router_Standard(
            $controllerFactory,
            $filesystem,
            $app,
            $testArea,
            $testBaseController,
            $backendSession,
            $helper,
            $stateModel,
            $configuration
        );
        $router->setFront($frontController);
        return $router;
    }
}
