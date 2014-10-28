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
namespace Magento\Checkout\Helper;

class ExpressRedirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_actionFlag;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * Customer session
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_context;

    /**
     * @var \Magento\Checkout\Helper\ExpressRedirect
     */
    protected $_helper;

    public function setUp()
    {
        $this->_actionFlag = $this->getMockBuilder(
            'Magento\Framework\App\ActionFlag'
        )->disableOriginalConstructor()->setMethods(
            array('set')
        )->getMock();

        $this->_objectManager = $this->getMockBuilder(
            'Magento\Framework\ObjectManager'
        )->disableOriginalConstructor()->setMethods(
            array('get', 'setFactory', 'create', 'configure')
        )->getMock();

        $this->_customerSession = $this->getMockBuilder(
            'Magento\Customer\Model\Session'
        )->disableOriginalConstructor()->setMethods(
            array('setBeforeAuthUrl')
        )->getMock();

        $this->_context = $this->getMockBuilder('Magento\Framework\App\Helper\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_helper = new \Magento\Checkout\Helper\ExpressRedirect(
            $this->_actionFlag,
            $this->_objectManager,
            $this->_customerSession,
            $this->_context
        );
    }

    /**
     * @dataProvider redirectLoginDataProvider
     * @param array $actionFlagList
     * @param string|null $customerBeforeAuthUrl
     * @param string|null $customerBeforeAuthUrlDefault
     */
    public function testRedirectLogin($actionFlagList, $customerBeforeAuthUrl, $customerBeforeAuthUrlDefault)
    {
        $expressRedirectMock = $this->getMockBuilder(
            'Magento\Checkout\Controller\Express\RedirectLoginInterface'
        )->disableOriginalConstructor()->setMethods(
            array(
                'getActionFlagList',
                'getResponse',
                'getCustomerBeforeAuthUrl',
                'getLoginUrl',
                'getRedirectActionName'
            )
        )->getMock();
        $expressRedirectMock->expects(
            $this->any()
        )->method(
            'getActionFlagList'
        )->will(
            $this->returnValue($actionFlagList)
        );

        $atIndex = 0;
        $actionFlagList = array_merge(array('no-dispatch' => true), $actionFlagList);
        foreach ($actionFlagList as $actionKey => $actionFlag) {
            $this->_actionFlag->expects($this->at($atIndex))->method('set')->with('', $actionKey, $actionFlag);
            $atIndex++;
        }

        $expectedLoginUrl = 'loginURL';
        $expressRedirectMock->expects(
            $this->once()
        )->method(
            'getLoginUrl'
        )->will(
            $this->returnValue($expectedLoginUrl)
        );

        $urlMock = $this->getMockBuilder(
            'Magento\Core\Helper\Url'
        )->disableOriginalConstructor()->setMethods(
            array('addRequestParam')
        )->getMock();
        $urlMock->expects(
            $this->once()
        )->method(
            'addRequestParam'
        )->with(
            $expectedLoginUrl,
            array('context' => 'checkout')
        )->will(
            $this->returnValue($expectedLoginUrl)
        );

        $this->_objectManager->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Magento\Core\Helper\Url'
        )->will(
            $this->returnValue($urlMock)
        );

        $responseMock = $this->getMockBuilder(
            'Magento\Framework\App\Response\Http'
        )->disableOriginalConstructor()->setMethods(
            ['setRedirect', '__wakeup']
        )->getMock();
        $responseMock->expects($this->once())->method('setRedirect')->with($expectedLoginUrl);

        $expressRedirectMock->expects($this->once())->method('getResponse')->will($this->returnValue($responseMock));

        $expressRedirectMock->expects(
            $this->any()
        )->method(
            'getCustomerBeforeAuthUrl'
        )->will(
            $this->returnValue($customerBeforeAuthUrl)
        );
        $expectedCustomerBeforeAuthUrl = !is_null(
            $customerBeforeAuthUrl
        ) ? $customerBeforeAuthUrl : $customerBeforeAuthUrlDefault;
        if ($expectedCustomerBeforeAuthUrl) {
            $this->_customerSession->expects(
                $this->once()
            )->method(
                'setBeforeAuthUrl'
            )->with(
                $expectedCustomerBeforeAuthUrl
            );
        }
        $this->_helper->redirectLogin($expressRedirectMock, $customerBeforeAuthUrlDefault);
    }

    /**
     * Data provider
     * @return array
     */
    public function redirectLoginDataProvider()
    {
        return array(
            array(array(), 'beforeCustomerUrl', 'beforeCustomerUrlDEFAULT'),
            array(array('actionKey' => true), null, 'beforeCustomerUrlDEFAULT'),
            array(array(), 'beforeCustomerUrl', null)
        );
    }
}
