<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;

/**
 * Test class for \Magento\Backend\Block\Menu
 */
class MenuTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Menu $blockMenu
     */
    protected $blockMenu;

    /** @var \Magento\Framework\App\Cache\Type\Config $configCacheType */
    protected $configCacheType;

    protected function setUp()
    {
        $this->configCacheType = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Cache\Type\Config'
        );
        $this->configCacheType->save('', \Magento\Backend\Model\Menu\Config::CACHE_MENU_OBJECT);

        $this->blockMenu = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Block\Menu'
        );
    }

    /**
     * Verify that Admin Navigation Menu has valid structure format:
     *  - navigation menu wrapper contains id & role;
     *  - 1st level item is located on the nex level, contains link & title;
     *  - 2nd level item is located on the next level, contains title;
     *  - 3rd level items are located on the next level, contain links & titles, have valid sort order against
     *      each other;
     *  - mentioned above points are specified in valid order in scope of all html block.
     */
    public function testRenderNavigation()
    {
        $menuConfig = $this->prepareMenuConfig();
        $menuHtml = $this->blockMenu->renderNavigation($menuConfig->getMenu());

        $this->assertRegExp(
            '~.*id="nav".*role="menubar".*' .
                'role="menu-item".*a href=".*System.*' .
                'class="submenu".*role="menu".*Report.*' .
                'class="submenu".*role="menu".*a href=".*Private Sales.*a href=".*Invite.*a href=".*Invited Customers~',
            $menuHtml,
            'Admin Navigation Menu structure is invalid.'
        );
    }

    /**
     * @return \Magento\Backend\Model\Menu\Config
     */
    protected function prepareMenuConfig()
    {
        $this->loginAdminUser();

        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(
            [
                Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [
                    DirectoryList::MODULES => ['path' => __DIR__ . '/_files/menu'],
                ],
            ]
        );

        /* @var $validationState \Magento\Framework\App\Arguments\ValidationState */
        $validationState = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Arguments\ValidationState',
            ['appMode' => State::MODE_DEFAULT]
        );

        /* @var $configReader \Magento\Backend\Model\Menu\Config\Reader */
        $configReader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Menu\Config\Reader',
            ['validationState' => $validationState]
        );

        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Menu\Config',
            [
                'configReader' => $configReader,
                'configCacheType' => $this->configCacheType
            ]
        );
    }

    /**
     * @return void
     */
    protected function loginAdminUser()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Backend\Model\UrlInterface')
            ->turnOffSecretKey();

        $auth = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Backend\Model\Auth');
        $auth->login(\Magento\TestFramework\Bootstrap::ADMIN_NAME, \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD);
    }

    /**
     * @return void
     */
    protected function tearDown()
    {
        $this->configCacheType->save('', \Magento\Backend\Model\Menu\Config::CACHE_MENU_OBJECT);
    }
}
