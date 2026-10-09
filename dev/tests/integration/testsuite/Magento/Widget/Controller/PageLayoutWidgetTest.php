<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Widget\Controller;

use Magento\Cms\Block\Widget\Block as CmsBlockWidget;
use Magento\Cms\Test\Fixture\Block as BlockFixture;
use Magento\Cms\Test\Fixture\Page as PageFixture;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\Widget\Model\Widget\Instance;
use Magento\Widget\Model\Widget\InstanceFactory;

/**
 * A widget instance assigned to a page layout renders on pages using that layout.
 */
#[AppArea('frontend')]
class PageLayoutWidgetTest extends AbstractController
{
    /**
     * @var Instance|null
     */
    private $widgetInstance;

    protected function tearDown(): void
    {
        if ($this->widgetInstance !== null && $this->widgetInstance->getId()) {
            $this->widgetInstance->delete();
        }
        $this->cleanLayoutCache();
        parent::tearDown();
    }

    #[
        DataFixture(BlockFixture::class, ['content' => 'PageLayoutWidgetContent9537'], 'block'),
        DataFixture(PageFixture::class, ['page_layout' => '1column'], 'page'),
    ]
    public function testWidgetAssignedToPageLayoutRendersOnPageWithThatLayout(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $this->widgetInstance = $this->saveWidgetForPageLayout(
            (int)$fixtures->get('block')->getId(),
            '1column'
        );
        $this->cleanLayoutCache();

        $this->dispatch('/' . $fixtures->get('page')->getIdentifier());

        $body = (string)$this->getResponse()->getBody();
        $this->assertStringContainsString('page-layout-1column', $body);
        $this->assertStringContainsString('PageLayoutWidgetContent9537', $body);
    }

    #[
        DataFixture(BlockFixture::class, ['content' => 'PageLayoutWidgetContent9537'], 'block'),
        DataFixture(PageFixture::class, ['page_layout' => '2columns-left'], 'page'),
    ]
    public function testWidgetAssignedToPageLayoutDoesNotRenderOnLayoutInheritingIt(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $this->widgetInstance = $this->saveWidgetForPageLayout(
            (int)$fixtures->get('block')->getId(),
            '1column'
        );
        $this->cleanLayoutCache();

        $this->dispatch('/' . $fixtures->get('page')->getIdentifier());

        $body = (string)$this->getResponse()->getBody();
        $this->assertStringContainsString('page-layout-2columns-left', $body);
        $this->assertStringNotContainsString('PageLayoutWidgetContent9537', $body);
    }

    private function saveWidgetForPageLayout(int $blockId, string $pageLayout): Instance
    {
        $store = $this->_objectManager->get(StoreManagerInterface::class)->getDefaultStoreView();
        $themeKey = $this->_objectManager->get(DesignInterface::class)
            ->getConfigurationDesignTheme('frontend', ['store' => $store->getId()]);
        $theme = $this->_objectManager->get(FlyweightFactory::class)->create($themeKey, 'frontend');

        $widgetInstance = $this->_objectManager->get(InstanceFactory::class)->create();
        $widgetInstance->setData(
            [
                'instance_type' => CmsBlockWidget::class,
                'instance_code' => 'cms_static_block',
                'theme_id' => $theme->getId(),
                'title' => 'Page layout widget 9537',
                'store_ids' => ['0'],
                'sort_order' => '0',
                'widget_parameters' => ['block_id' => (string)$blockId],
                'page_groups' => [
                    [
                        'page_group' => 'page_layouts',
                        'page_layouts' => [
                            'page_id' => '0',
                            'layout_handle' => $pageLayout,
                            'for' => 'all',
                            'block' => 'content',
                            'template' => 'widget/static_block/default.phtml',
                        ],
                    ],
                ],
            ]
        );
        $widgetInstance->save();

        return $widgetInstance;
    }

    private function cleanLayoutCache(): void
    {
        $typeList = $this->_objectManager->get(TypeListInterface::class);
        $typeList->cleanType('layout');
        $typeList->cleanType('full_page');
    }
}
