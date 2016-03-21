<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Auth;

/**
 * @magentoAppArea adminhtml
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        $this->_auth = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Auth'
        );
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Auth\Session'
        );
        $this->_auth->setAuthStorage($this->_model);
    }

    protected function tearDown()
    {
        $this->_model = null;
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            null
        );
    }

    /**
     * @dataProvider loginDataProvider
     */
    public function testIsLoggedIn($loggedIn)
    {
        if ($loggedIn) {
            $this->_auth->login(
                \Magento\TestFramework\Bootstrap::ADMIN_NAME,
                \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
            );
        }
        $this->assertEquals($loggedIn, $this->_model->isLoggedIn());
    }

    public function loginDataProvider()
    {
        return [[true], [false]];
    }
}
