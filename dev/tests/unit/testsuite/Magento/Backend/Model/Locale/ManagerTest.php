<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->_session = $this->getMock('Magento\Backend\Model\Session', [], [], '', false);

        $this->_authSession = $this->getMock(
            'Magento\Backend\Model\Auth\Session',
            ['getUser'],
            [],
            '',
            false
        );

        $userMock = new \Magento\Framework\Object();

        $this->_authSession->expects($this->any())->method('getUser')->will($this->returnValue($userMock));

        $this->_translator = $this->getMock('Magento\Framework\TranslateInterface', [], [], '', false);

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
        return ['case1' => ['locale' => 'de_DE'], 'case2' => ['locale' => 'en_US']];
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
