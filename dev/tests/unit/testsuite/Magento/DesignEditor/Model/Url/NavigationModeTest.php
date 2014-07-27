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
namespace Magento\DesignEditor\Model\Url;

class NavigationModeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test route params
     */
    const FRONT_NAME = 'vde';

    const ROUTE_PATH = 'some-rout-url/page.html';

    const BASE_URL = 'http://test.com';

    /**
     * @var \Magento\DesignEditor\Model\Url\NavigationMode
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_designHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_routeParamsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeResolverMock;

    /**
     * @var array
     */
    protected $_testData = array('themeId' => 1, 'mode' => 'test');

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_designHelperMock = $this->getMock('Magento\DesignEditor\Helper\Data', array(), array(), '', false);
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getAlias'
        )->will(
            $this->returnValueMap(array(array('editorMode', 'navigation'), array('themeId', 1)))
        );

        $this->_routeParamsMock = $this->getMock(
            'Magento\Framework\Url\RouteParamsResolverFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_routeParamsMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue(
                $this->getMock('Magento\Core\Model\Url\RouteParamsResolver', array(), array(), '', false)
            )
        );

        $this->_scopeResolverMock =  $this->getMock(
            'Magento\Framework\Url\ScopeResolverInterface',
            array(),
            array(),
            '',
            false
        );

        $this->_model = $objectManagerHelper->getObject(
            'Magento\DesignEditor\Model\Url\NavigationMode',
            array(
                'helper' => $this->_designHelperMock,
                'data' => $this->_testData,
                'routeParamsResolver' => $this->_routeParamsMock,
                'scopeResolver' => $this->_scopeResolverMock
            )
        );
    }

    public function testConstruct()
    {
        $this->assertAttributeEquals($this->_designHelperMock, '_helper', $this->_model);
        $this->assertAttributeEquals($this->_testData, '_data', $this->_model);
    }

    public function testGetRouteUrl()
    {
        $this->_designHelperMock->expects(
            $this->any()
        )->method(
            'getFrontName'
        )->will(
            $this->returnValue(self::FRONT_NAME)
        );

        $store = $this->getMock(
            'Magento\Store\Model\Store',
            array('getBaseUrl', 'isAdmin', 'isAdminUrlSecure', 'isFrontUrlSecure', '__sleep', '__wakeup'),
            array(),
            '',
            false
        );
        $store->expects($this->any())->method('getBaseUrl')->will($this->returnValue(self::BASE_URL));

        $store->expects($this->any())->method('isAdmin')->will($this->returnValue(false));

        $store->expects($this->any())->method('isAdminUrlSecure')->will($this->returnValue(false));

        $store->expects($this->any())->method('isFrontUrlSecure')->will($this->returnValue(false));

        $this->_model->setData('scope', $store);
        $this->_model->setData('type', null);
        $this->_model->setData('route_front_name', self::FRONT_NAME);

        $sourceUrl = self::BASE_URL . '/' . self::ROUTE_PATH;
        $expectedUrl = self::BASE_URL .
            '/' .
            self::FRONT_NAME .
            '/' .
            $this->_testData['mode'] .
            '/' .
            $this->_testData['themeId'] .
            '/' .
            self::ROUTE_PATH;

        $this->_scopeResolverMock->expects(
            $this->any()
        )->method('getScope')->will($this->returnValue($store));

        $this->assertEquals($expectedUrl, $this->_model->getRouteUrl($sourceUrl));
        $this->assertEquals($expectedUrl, $this->_model->getRouteUrl($expectedUrl));
    }
}
