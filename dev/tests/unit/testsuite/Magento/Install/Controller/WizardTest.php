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
 * @category    Magento
 * @package     Magento_Install
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Install\Block\Wizard
 */
namespace Magento\Install\Controller;

/**
 * Class WizardTest
 *
 * @package Magento\Install\Controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WizardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Locale to test
     */
    const LOCALE = 'xx_XX';

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Install\Block\Locale
     */
    protected $_block;

    /**
     * @var \Magento\App\ViewInterface
     */
    protected $_viewMock;

    /**
     * @var \Magento\Install\Model\Installer
     */
    protected $_installerMock;

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layoutMock;

    /**
     * @var \Magento\Install\Controller\Wizard
     */
    protected $_controller;

    /**
     * @var \Magento\App\Action\Context
     */
    protected $_contextMock;

    /**
     * @var \Magento\Install\Model\Wizard
     */
    protected $_wizardMock;

    /**
     * @var \Magento\Session\Generic
     */
    protected $_sessionMock;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_requestMock;

    /**
     * @var \Magento\App\ResponseInterface
     */
    protected $_responseMock;

    /**
     * @var \Magento\App\ActionFlag
     */
    protected $_actionFlagMock;

    /**
     * @var \Magento\View\Element\Template\Context
     */
    protected $_blockContextMock;

    /**
     * Set up before test
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_installerMock = $this->getMock(
            '\Magento\Install\Model\Installer',
            array('isApplicationInstalled'),
            array(),
            '',
            false
        );
        $this->_installerMock->expects($this->any())->method('isApplicationInstalled')->will($this->returnValue(true));


        $this->_blockMock = $this->getMock('\Magento\Install\Block\Locale', array(), array(), '', false);



        $this->_layoutMock = $this->getMock(
            '\Magento\Core\Model\Layout',
            array('getBlock', 'initMessages', 'addBlock'),
            array(),
            '',
            false
        );

        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'initMessages'
        )->withAnyParameters()->will(
            $this->returnValue(true)
        );
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'addBlock'
        )->withAnyParameters()->will(
            $this->returnValue(true)
        );


        $this->_viewMock = $this->getMockForAbstractClass(
            '\Magento\App\ViewInterface',
            array(),
            '',
            false,
            false,
            true,
            array('getLayout')
        );
        $this->_viewMock->expects(
            $this->any()
        )->method(
            'getLayout'
        )->withAnyParameters()->will(
            $this->returnValue($this->_layoutMock)
        );

        $this->_requestMock = $this->_getClearMock('\Magento\App\RequestInterface');
        $this->_responseMock = $this->_getClearMock('\Magento\App\ResponseInterface');
        $this->_actionFlagMock = $this->_getClearMock('\Magento\App\ActionFlag');

        $this->_contextMock = $this->getMock(
            '\Magento\App\Action\Context',
            array('getView', 'getRequest', 'getResponse', 'getActionFlag'),
            array(),
            '',
            false
        );
        $this->_contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->_viewMock));
        $this->_contextMock->expects(
            $this->any()
        )->method(
            'getRequest'
        )->will(
            $this->returnValue($this->_requestMock)
        );
        $this->_contextMock->expects(
            $this->any()
        )->method(
            'getResponse'
        )->will(
            $this->returnValue($this->_responseMock)
        );
        $this->_contextMock->expects(
            $this->any()
        )->method(
            'getActionFlag'
        )->will(
            $this->returnValue($this->_actionFlagMock)
        );


        $this->_blockContextMock = $this->getMock(
            '\Magento\View\Element\Template\Context',
            array(),
            array(),
            '',
            false
        );



        $this->_wizardMock = $this->getMock(
            '\Magento\Install\Model\Wizard',
            array('getStepByRequest'),
            array(),
            '',
            false
        );
        $this->_wizardMock->expects(
            $this->any()
        )->method(
            'getStepByRequest'
        )->withAnyParameters()->will(
            $this->returnValue(false)
        );

        $this->_sessionMock = $this->getMock('\Magento\Session\Generic', array('getLocale'), array(), '', false);
        $this->_sessionMock->expects($this->any())->method('getLocale')->will($this->returnValue(self::LOCALE));

        $this->_block = $this->_objectManager->getObject(
            'Magento\Install\Block\Locale',
            array(
                'context' => $this->_blockContextMock,
                'installer' => $this->_installerMock,
                'installWizard' => $this->_wizardMock,
                'session' => $this->_sessionMock,
                'data' => array()
            )
        );

        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'getBlock'
        )->with(
            'install.locale'
        )->will(
            $this->returnValue($this->_block)
        );

        $this->_controller = $this->_objectManager->getObject(
            'Magento\Install\Controller\Wizard',
            array(
                'context' => $this->_contextMock,
                'configScope' => $this->_getClearMock('Magento\Config\Scope'),
                'installer' => $this->_getClearMock('Magento\Install\Model\Installer'),
                'wizard' => $this->_wizardMock,
                'session' => $this->_sessionMock,
                'dbUpdater' => $this->_getClearMock('Magento\Module\UpdaterInterface'),
                'storeManager' => $this->_getClearMock('Magento\Core\Model\StoreManagerInterface'),
                'appState' => $this->_getClearMock('Magento\App\State')
            )
        );
    }

    /**
     * Get clear mock
     *
     * @param string $className
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getClearMock($className)
    {
        return $this->getMock($className, array(), array(), '', false);
    }

    /**
     * Test setting locale from session
     */
    public function testLocaleAction()
    {
        $this->_controller->localeAction();
        $this->assertEquals(
            $this->_block->getLocaleCode(),
            self::LOCALE,
            'Failed asserting that locale is set from session'
        );
    }
}
