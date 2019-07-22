<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\Locale;

use Magento\Framework\Locale\Resolver;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;

/**
 * @magentoAppArea adminhtml
 */
class ResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        $this->_model = Bootstrap::getObjectManager()->create(
            \Magento\Backend\Model\Locale\Resolver::class
        );
    }

    /**
     * @covers \Magento\Backend\Model\Locale\Resolver::setLocale
     */
    public function testSetLocaleWithDefaultLocale()
    {
        $this->_checkSetLocale(Resolver::DEFAULT_LOCALE);
    }

    /**
     * @covers \Magento\Backend\Model\Locale\Resolver::setLocale
     */
    public function testSetLocaleWithBaseInterfaceLocale()
    {
        $user = Bootstrap::getObjectManager()->create(User::class);
        $session = Bootstrap::getObjectManager()->get(
            \Magento\Backend\Model\Auth\Session::class
        );
        $session->setUser($user);
        Bootstrap::getObjectManager()->get(
            \Magento\Backend\Model\Auth\Session::class
        )->getUser()->setInterfaceLocale(
            'fr_FR'
        );
        $this->_checkSetLocale('fr_FR');
    }

    /**
     * @covers \Magento\Backend\Model\Locale\Resolver::setLocale
     */
    public function testSetLocaleWithSessionLocale()
    {
        Bootstrap::getObjectManager()->get(
            \Magento\Backend\Model\Session::class
        )->setSessionLocale(
            'es_ES'
        );
        $this->_checkSetLocale('es_ES');
    }

    /**
     * @covers \Magento\Backend\Model\Locale\Resolver::setLocale
     */
    public function testSetLocaleWithRequestLocale()
    {
        $request = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\App\RequestInterface::class);
        $request->setPostValue(['locale' => 'de_DE']);
        $this->_checkSetLocale('de_DE');
    }

    /**
     * Check set locale
     *
     * @param string $localeCodeToCheck
     * @return void
     */
    protected function _checkSetLocale($localeCodeToCheck)
    {
        $this->_model->setLocale();
        $localeCode = $this->_model->getLocale();
        $this->assertEquals($localeCode, $localeCodeToCheck);
    }
}
