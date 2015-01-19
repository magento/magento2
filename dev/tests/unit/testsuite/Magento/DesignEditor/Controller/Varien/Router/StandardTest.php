<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Varien\Router;

class StandardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test area code
     */
    const AREA_CODE = 'frontend';

    /**
     * Test VDE front name prefix
     */
    const VDE_FRONT_NAME = 'test_front_name/test_mode_type/test_theme_id';

    /**
     * Test VDE configuration data
     */
    const VDE_CONFIGURATION_DATA = 'vde_config_data';

    /**
     * Test path and host
     */
    const TEST_PATH = '/customer/account';

    const TEST_HOST = 'http://test.domain';

    /**
     * @var \Magento\DesignEditor\Controller\Varien\Router\Standard
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieReaderInterface
     */
    protected $_cookieReaderMock;


    public function setUp()
    {
        $this->_cookieReaderMock = $this->getMock('Magento\Framework\Stdlib\Cookie\CookieReaderInterface');
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param bool $isVde
     * @param bool $isLoggedIn
     * @param array $routers
     * @param string|null $matchedValue
     *
     * @dataProvider matchDataProvider
     */
    public function testMatch(
        \Magento\Framework\App\RequestInterface $request,
        $isVde,
        $isLoggedIn,
        array $routers = [],
        $matchedValue = null
    ) {
        $this->_model = $this->_prepareMocksForTestMatch($request, $isVde, $isLoggedIn, $routers);
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
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_cookieReaderMock = $this->getMock('Magento\Framework\Stdlib\Cookie\CookieReaderInterface');
        $uri    = self::TEST_HOST . '/' . self::VDE_FRONT_NAME . self::TEST_PATH;
        $notVdeUrl = self::TEST_HOST . self::TEST_PATH;

        $excludedRouters = ['admin' => 'admin router', 'vde' => 'vde router'];

        $routerListMock = $this->getMock(
            '\Magento\Framework\App\Route\ConfigInterface\Proxy',
            [],
            [],
            '',
            false
        );

        $infoProcessorMock = $this->getMock('Magento\Framework\App\Request\PathInfoProcessorInterface');
        $infoProcessorMock->expects($this->any())->method('process')->will($this->returnArgument(1));

        // test data to verify routers match logic
        $matchedRequest = $this->getMock(
            'Magento\Framework\App\Request\Http',
            ['_isFrontArea'],
            [$routerListMock, $infoProcessorMock, $this->_cookieReaderMock, $objectManagerMock, $uri]
        );

        $matchedController = $this->getMockForAbstractClass(
            'Magento\Framework\App\Action\AbstractAction',
            [],
            '',
            false
        );

        // method "match" will be invoked for this router because it's first in the list
        $matchedRouter = $this->getMock('Magento\Core\App\Router\Base', [], [], '', false);
        $matchedRouter->expects(
            $this->once()
        )->method(
            'match'
        )->with(
            $matchedRequest
        )->will(
            $this->returnValue($matchedController)
        );
        $matchedRouter->expects($this->once())
            ->method('match')
            ->with($matchedRequest)
            ->will($this->returnValue($matchedController));

        // method "match" will not be invoked for this router because controller will be found by first router
        $notMatchedRouter = $this->getMock('Magento\Core\App\Router\Base', [], [], '', false);
        $notMatchedRouter->expects($this->never())->method('match');

        $matchedRouters = array_merge(
            $excludedRouters,
            ['matched' => $matchedRouter, 'not_matched' => $notMatchedRouter]
        );

        $routers = [
            'not vde request' => [
                '$request' => $this->getMock(
                        'Magento\Framework\App\Request\Http', ['_isFrontArea'], [
                            $routerListMock,
                            $infoProcessorMock,
                            $this->_cookieReaderMock,
                            $objectManagerMock,
                            $notVdeUrl
                        ]
                    ),
                '$isVde'           => false,
                '$isLoggedIn'      => true,
            ],
            'not logged as admin' => [
                '$request' => $this->getMock(
                        'Magento\Framework\App\Request\Http',
                        ['_isFrontArea'],
                        [$routerListMock, $infoProcessorMock, $this->_cookieReaderMock, $objectManagerMock, $uri]
                    ),
                '$isVde'           => true,
                '$isLoggedIn'      => false,
            ],
            'no matched routers' => [
                '$request' => $this->getMock(
                        'Magento\Framework\App\Request\Http',
                        ['_isFrontArea'],
                        [$routerListMock, $infoProcessorMock, $this->_cookieReaderMock, $objectManagerMock, $uri]
                    ),
                '$isVde'           => true,
                '$isLoggedIn'      => true,
                '$routers'         => $excludedRouters
            ],
            'matched routers' => [
                '$request' => $matchedRequest,
                '$isVde' => true,
                '$isLoggedIn' => true,
                '$routers' => $matchedRouters,
                '$matchedValue' => $matchedController
            ]
        ];
        return $routers;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param bool $isVde
     * @param bool $isLoggedIn
     * @param array $routers
     * @return \Magento\DesignEditor\Controller\Varien\Router\Standard
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _prepareMocksForTestMatch(
        \Magento\Framework\App\RequestInterface $request,
        $isVde,
        $isLoggedIn,
        array $routers
    ) {
        // default mocks - not affected on method functionality
        $helperMock = $this->_getHelperMock($isVde);
        $backendSessionMock = $this->_getBackendSessionMock($isVde, $isLoggedIn);
        $stateMock = $this->_getStateModelMock($routers);
        $routerListMock = $this->getMock(
            'Magento\Framework\App\RouterList',
            [
                'current',
                'next',
                'key',
                'valid',
                'rewind'
            ],
            [
                'routerList' => $routers
            ],
            '',
            false
        );
        if (array_key_exists('matched', $routers)) {
            $routerListMock = $this->mockIterator($routerListMock, $routers, true);
        }
        $router = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\DesignEditor\Controller\Varien\Router\Standard',
            [
                'routerId' => 'frontend',
                'routerList' => $routerListMock,
                'designEditorHelper' => $helperMock,
                'designEditorState' => $stateMock,
                'session' => $backendSessionMock
            ]
        );
        return $router;
    }

    /**
     * @param bool $isVde
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getHelperMock($isVde)
    {
        $helper = $this->getMock('Magento\DesignEditor\Helper\Data', ['isVdeRequest'], [], '', false);
        $helper->expects($this->any())
            ->method('isVdeRequest')
            ->will($this->returnValue($isVde));
        return $helper;
    }

    /**
     * @param bool $isVde
     * @param bool $isLoggedIn
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getBackendSessionMock($isVde, $isLoggedIn)
    {
        $backendSession = $this->getMock('Magento\Backend\Model\Auth\Session', [], [], '', false);
        $backendSession->expects($isVde ? $this->any() : $this->never())
            ->method('isLoggedIn')
            ->will($this->returnValue($isLoggedIn));

        return $backendSession;
    }

    /**
     * @param array $routers
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getStateModelMock(array $routers)
    {
        $stateModel = $this->getMock('Magento\DesignEditor\Model\State', [], [], '', false);

        if (array_key_exists('matched', $routers)) {
            $stateModel->expects($this->once())->method('update')->with(self::AREA_CODE);
        }

        return $stateModel;
    }

    /**
     * Mock for Iterator class
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $iteratorMock
     * @param array $items
     * @param bool $includeCallsToKey
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function mockIterator(
        \PHPUnit_Framework_MockObject_MockObject $iteratorMock,
        array $items,
        $includeCallsToKey = true
    ) {
        $iteratorMock->expects($this->at(0))
            ->method('rewind');
        $i = 0;
        foreach ($items as $key => $value) {
            $iteratorMock->expects($this->at($i))
                ->method('valid')
                ->will($this->returnValue(true));
            $iteratorMock->expects($this->at($i))
                ->method('current')
                ->will($this->returnValue($value));
            if ($includeCallsToKey) {
                $iteratorMock->expects($this->at($i))
                    ->method('key')
                    ->will($this->returnValue($key));
            }
            $iteratorMock->expects($this->at($i))
                ->method('next')
                ->will($this->returnValue($value));
            $iteratorMock->expects($this->at($i))
                ->method('valid')
                ->will($this->returnValue(false));
            ++$i;
        }

        return $iteratorMock;
    }
}
