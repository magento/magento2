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
namespace Magento\Backend\Model\Locale;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Locale\Manager
     */
    protected $_model;

    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_translator;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    protected function setUp()
    {
        $this->_session = $this->getMock('Magento\Backend\Model\Session', array(), array(), '', false);

        $this->_authSession = $this->getMock(
            'Magento\Backend\Model\Auth\Session',
            array('getUser'),
            array(),
            '',
            false
        );

        $userMock = new \Magento\Framework\Object();

        $this->_authSession->expects($this->any())->method('getUser')->will($this->returnValue($userMock));

        $this->_translator = $this->getMock('Magento\Framework\TranslateInterface', array(), array(), '', false);

        $this->_translator->expects($this->any())->method('setLocale')->will($this->returnValue($this->_translator));

        $this->_translator->expects($this->any())->method('init')->will($this->returnValue(false));

        $this->_model = new \Magento\Backend\Model\Locale\Manager(
            $this->_session,
            $this->_authSession,
            $this->_translator
        );
    }

    /**
     * @return array
     */
    public function switchBackendInterfaceLocaleDataProvider()
    {
        return array('case1' => array('locale' => 'de_DE'), 'case2' => array('locale' => 'en_US'));
    }

    /**
     * @param string $locale
     * @dataProvider switchBackendInterfaceLocaleDataProvider
     * @covers \Magento\Backend\Model\Locale\Manager::switchBackendInterfaceLocale
     */
    public function testSwitchBackendInterfaceLocale($locale)
    {
        $this->_model->switchBackendInterfaceLocale($locale);

        $userInterfaceLocale = $this->_authSession->getUser()->getInterfaceLocale();
        $this->assertEquals($userInterfaceLocale, $locale);

        $sessionLocale = $this->_session->getSessionLocale();
        $this->assertEquals($sessionLocale, null);
    }

    /**
     * @covers \Magento\Backend\Model\Locale\Manager::getUserInterfaceLocale
     */
    public function testGetUserInterfaceLocaleDefault()
    {
        $locale = $this->_model->getUserInterfaceLocale();

        $this->assertEquals($locale, \Magento\Framework\Locale\ResolverInterface::DEFAULT_LOCALE);
    }

    /**
     * @covers \Magento\Backend\Model\Locale\Manager::getUserInterfaceLocale
     */
    public function testGetUserInterfaceLocale()
    {
        $this->_model->switchBackendInterfaceLocale('de_DE');
        $locale = $this->_model->getUserInterfaceLocale();

        $this->assertEquals($locale, 'de_DE');
    }
}
