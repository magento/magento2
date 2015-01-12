<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
    protected $_testData = ['themeId' => 1, 'mode' => 'test'];

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_designHelperMock = $this->getMock('Magento\DesignEditor\Helper\Data', [], [], '', false);
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getAlias'
        )->will(
            $this->returnValueMap([['editorMode', 'navigation'], ['themeId', 1]])
        );

        $this->_routeParamsMock = $this->getMock(
            'Magento\Framework\Url\RouteParamsResolverFactory',
            [],
            [],
            '',
            false
        );
        $this->_routeParamsMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue(
                $this->getMock('Magento\Core\Model\Url\RouteParamsResolver', [], [], '', false)
            )
        );

        $this->_scopeResolverMock =  $this->getMock(
            'Magento\Framework\Url\ScopeResolverInterface',
            [],
            [],
            '',
            false
        );

        $this->_model = $objectManagerHelper->getObject(
            'Magento\DesignEditor\Model\Url\NavigationMode',
            [
                'helper' => $this->_designHelperMock,
                'data' => $this->_testData,
                'routeParamsResolver' => $this->_routeParamsMock,
                'scopeResolver' => $this->_scopeResolverMock
            ]
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
            ['getBaseUrl', 'isAdmin', 'isAdminUrlSecure', 'isFrontUrlSecure', '__sleep', '__wakeup'],
            [],
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
