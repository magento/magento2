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
namespace Magento\Core\Model\Resource\Layout;

class UpdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Resource\Layout\Update
     */
    protected $_resourceModel;

    protected function setUp()
    {
        $this->_resourceModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Core\Model\Resource\Layout\Update'
        );
    }

    /**
     * @magentoDataFixture Magento/Core/_files/layout_update.php
     */
    public function testFetchUpdatesByHandle()
    {
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Design\ThemeInterface'
        );
        $theme->load('Test Theme', 'theme_title');
        $result = $this->_resourceModel->fetchUpdatesByHandle(
            'test_handle',
            $theme,
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\StoreManagerInterface'
            )->getStore()
        );
        $this->assertEquals('not_temporary', $result);
    }

    /**
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_enabled.php
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/application_cache.php
     * @magentoDataFixture Magento/Core/_files/layout_cache.php
     */
    public function testSaveAfterClearCache()
    {
        /** @var $appCache \Magento\Framework\App\Cache */
        $appCache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\Cache');
        /** @var \Magento\Framework\App\Cache\Type\Layout $layoutCache */
        $layoutCache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\Cache\Type\Layout'
        );

        $this->assertNotEmpty($appCache->load('APPLICATION_FIXTURE'));
        $this->assertNotEmpty($layoutCache->load('LAYOUT_CACHE_FIXTURE'));

        /** @var $layoutUpdate \Magento\Core\Model\Layout\Update */
        $layoutUpdate = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Core\Model\Layout\Update'
        );
        $this->_resourceModel->save($layoutUpdate);

        $this->assertNotEmpty($appCache->load('APPLICATION_FIXTURE'), 'Non-layout cache must be kept');
        $this->assertFalse($layoutCache->load('LAYOUT_CACHE_FIXTURE'), 'Layout cache must be erased');
    }
}
