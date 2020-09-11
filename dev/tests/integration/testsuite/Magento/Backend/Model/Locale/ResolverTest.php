<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Locale;

use Magento\Framework\Locale\Resolver;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class ResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_model;

    protected function setUp(): void
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
        $user = new \Magento\Framework\DataObject();
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
     * Tests setLocale() with parameter
     *
     * @param string|null $localeParam
     * @param string|null $localeRequestParam
     * @param string $localeExpected
     * @dataProvider setLocaleWithParameterDataProvider
     */
    public function testSetLocaleWithParameter(
        ?string $localeParam,
        ?string $localeRequestParam,
        string $localeExpected
    ) {
        $request = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\App\RequestInterface::class);
        $request->setPostValue(['locale' => $localeRequestParam]);
        $this->_model->setLocale($localeParam);
        $this->assertEquals($localeExpected, $this->_model->getLocale());
    }

    /**
     * @return array
     */
    public function setLocaleWithParameterDataProvider(): array
    {
        return [
            ['ko_KR', 'ja_JP', 'ja_JP'],
            ['ko_KR', null, 'ko_KR'],
            [null, 'ja_JP', 'ja_JP'],
        ];
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
