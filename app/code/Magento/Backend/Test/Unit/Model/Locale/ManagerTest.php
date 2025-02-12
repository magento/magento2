<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Locale;

use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\Locale\Manager;
use Magento\Backend\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\TranslateInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    /**
     * @var Manager
     */
    private $_model;

    /**
     * @var MockObject|TranslateInterface
     */
    private $_translator;

    /**
     * @var Session
     */
    private $_session;

    /**
     * @var MockObject|\Magento\Backend\Model\Auth\Session
     */
    private $_authSession;

    /**
     * @var MockObject|ConfigInterface
     */
    private $_backendConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_session = $this->createMock(Session::class);

        $this->_authSession = $this->getMockBuilder(\Magento\Backend\Model\Auth\Session::class)->addMethods(['getUser'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_backendConfig = $this->getMockForAbstractClass(
            ConfigInterface::class,
            [],
            '',
            false
        );

        $userMock = new DataObject();

        $this->_authSession->expects($this->any())->method('getUser')->willReturn($userMock);

        $this->_translator = $this->getMockBuilder(TranslateInterface::class)
            ->addMethods(['init'])
            ->onlyMethods(['setLocale'])
            ->getMockForAbstractClass();

        $this->_translator->expects($this->any())->method('setLocale')->willReturn($this->_translator);

        $this->_translator->expects($this->any())->method('init')->willReturn(false);

        $this->_model = new Manager(
            $this->_session,
            $this->_authSession,
            $this->_translator,
            $this->_backendConfig
        );
    }

    /**
     * @return array
     */
    public static function switchBackendInterfaceLocaleDataProvider()
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

        $this->assertEquals($locale, Resolver::DEFAULT_LOCALE);
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

    /**
     * @covers \Magento\Backend\Model\Locale\Manager::getUserInterfaceLocale
     */
    public function testGetUserInterfaceGeneralLocale()
    {
        $this->_backendConfig->expects($this->any())
            ->method('getValue')
            ->with('general/locale/code')
            ->willReturn('test_locale');
        $locale = $this->_model->getUserInterfaceLocale();
        $this->assertEquals($locale, 'test_locale');
    }
}
