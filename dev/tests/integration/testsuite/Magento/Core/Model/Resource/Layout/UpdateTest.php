<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
                'Magento\Store\Model\StoreManagerInterface'
            )->getStore()
        );
        $this->assertEquals('not_temporary', $result);
    }

    /**
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
        $layoutUpdate->setHasDataChanges(true);
        $this->_resourceModel->save($layoutUpdate);

        $this->assertNotEmpty($appCache->load('APPLICATION_FIXTURE'), 'Non-layout cache must be kept');
        $this->assertFalse($layoutCache->load('LAYOUT_CACHE_FIXTURE'), 'Layout cache must be erased');
    }
}
