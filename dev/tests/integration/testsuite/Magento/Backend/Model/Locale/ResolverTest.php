<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Locale;

use Magento\Framework\Locale\Resolver;

/**
 * @magentoAppArea adminhtml
 */
class ResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Locale\Resolver'
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
        $user = new \Magento\Framework\DataObject();
        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\Auth\Session'
        );
        $session->setUser($user);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\Auth\Session'
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
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\Session'
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
        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\App\RequestInterface');
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
