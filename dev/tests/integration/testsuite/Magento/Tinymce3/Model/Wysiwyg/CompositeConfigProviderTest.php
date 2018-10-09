<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Tinymce3\Model\Wysiwyg;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\AuthorizationInterface;

/**
 * @magentoAppArea adminhtml
 */
class CompositeConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test enabled module is able to modify WYSIWYG config
     *
     * @return void
     *
     * @magentoConfigFixture default/cms/wysiwyg/editor Magento_Tinymce3/tinymce3Adapter
     */
    public function testTestModuleEnabledModuleIsAbleToModifyConfig()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->configure([
            'preferences' => [
                AuthorizationInterface::class => \Magento\Backend\Model\Search\AuthorizationMock::class
            ]
        ]);
        $compositeConfigProvider = $objectManager->create(\Magento\Cms\Model\Wysiwyg\CompositeConfigProvider::class);
        $model = $objectManager->create(
            \Magento\Cms\Model\Wysiwyg\Config::class,
            ['configProvider' => $compositeConfigProvider]
        );
        $config = $model->getConfig();
        $this->assertArrayHasKey('add_images', $config);
        $this->assertArrayHasKey('files_browser_window_url', $config);
        $this->assertTrue($config['add_images']);
    }
}
