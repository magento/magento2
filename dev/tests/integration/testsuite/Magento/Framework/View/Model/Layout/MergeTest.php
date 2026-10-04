<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Model\Layout;

use Magento\Framework\View\EntitySpecificHandlesList;
use Magento\Framework\View\Layout\LayoutCacheKeyInterface;
use PHPUnit\Framework\Attributes\DataProvider;

class MergeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Fixture XML instruction(s) to be used in tests
     */
    public const FIXTURE_LAYOUT_XML
        = '<block class="Magento\Framework\View\Element\Template" template="Magento_Framework::fixture.phtml"/>';

    /**
     * @var \Magento\Framework\View\Model\Layout\Merge
     */
    protected $model;

    /**
     * @var LayoutCacheKeyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutCacheKeyMock;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = $objectManager->create(\Magento\Framework\View\Design\ThemeInterface::class);
        $theme->load(1);

        /** @var $layoutUpdate1 \Magento\Widget\Model\Layout\Update */
        $layoutUpdate1 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Widget\Model\Layout\Update::class
        );
        $layoutUpdate1->setHandle('fixture_handle_one');
        $layoutUpdate1->setXml(
            '<body>
                <block class="Magento\Framework\View\Element\Template" 
                       template="Magento_Framework::fixture_template_one.phtml"/>
            </body>'
        );
        $layoutUpdate1->setHasDataChanges(true);
        $layoutUpdate1->save();
        $link1 = $objectManager->create(\Magento\Widget\Model\Layout\Link::class);
        $link1->setThemeId($theme->getId());
        $link1->setLayoutUpdateId($layoutUpdate1->getId());
        $link1->save();

        /** @var $layoutUpdate2 \Magento\Widget\Model\Layout\Update */
        $layoutUpdate2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Widget\Model\Layout\Update::class
        );
        $layoutUpdate2->setHandle('fixture_handle_two');
        $layoutUpdate2->setXml(
            '<body>
                <block class="Magento\Framework\View\Element\Template"
                       template="Magento_Framework::fixture_template_two.phtml"/>
            </body>'
        );
        $layoutUpdate2->setHasDataChanges(true);
        $layoutUpdate2->save($layoutUpdate2);
        $link2 = $objectManager->create(\Magento\Widget\Model\Layout\Link::class);
        $link2->setThemeId($theme->getId());
        $link2->setLayoutUpdateId($layoutUpdate2->getId());
        $link2->save();

        $this->layoutCacheKeyMock = $this->createMock(LayoutCacheKeyInterface::class);
        $this->layoutCacheKeyMock->expects($this->any())
            ->method('getCacheKeys')
            ->willReturn([]);

        $this->model = $objectManager->create(
            \Magento\Framework\View\Model\Layout\Merge::class,
            [
                'theme' => $theme,
                'layoutCacheKey' => $this->layoutCacheKeyMock,
            ]
        );
    }

    /**
     * Two products with no widget targeting either must share a single layout cache entry.
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetCacheIdIsIdenticalForProductsWithNoWidgetCustomisation(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $theme = $objectManager->create(\Magento\Framework\View\Design\ThemeInterface::class);
        $theme->load(1);

        $entitySpecificHandlesList = $objectManager->get(EntitySpecificHandlesList::class);
        $entitySpecificHandlesList->addHandle('catalog_product_view_id_1');
        $entitySpecificHandlesList->addHandle('catalog_product_view_id_2');

        $buildModel = function (array $handles) use ($objectManager, $theme): Merge {
            $model = $objectManager->create(
                \Magento\Framework\View\Model\Layout\Merge::class,
                ['theme' => $theme]
            );
            $model->addHandle($handles);
            return $model;
        };

        $productA = $buildModel(['default', 'catalog_product_view', 'catalog_product_view_id_1']);
        $productB = $buildModel(['default', 'catalog_product_view', 'catalog_product_view_id_2']);

        $this->assertSame($productA->getCacheId(), $productB->getCacheId());
    }

    /**
     * Cache key must diverge when a widget targets one product's entity-specific handle.
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetCacheIdDiffersWhenOneProductHasWidgetCustomisation(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $theme = $objectManager->create(\Magento\Framework\View\Design\ThemeInterface::class);
        $theme->load(1);

        $layoutUpdate = $objectManager->create(\Magento\Widget\Model\Layout\Update::class);
        $layoutUpdate->setHandle('catalog_product_view_id_1');
        $layoutUpdate->setXml('<body></body>');
        $layoutUpdate->setHasDataChanges(true);
        $layoutUpdate->save();

        $link = $objectManager->create(\Magento\Widget\Model\Layout\Link::class);
        $link->setThemeId($theme->getId());
        $link->setLayoutUpdateId($layoutUpdate->getId());
        $link->save();

        $entitySpecificHandlesList = $objectManager->get(EntitySpecificHandlesList::class);
        $entitySpecificHandlesList->addHandle('catalog_product_view_id_1');
        $entitySpecificHandlesList->addHandle('catalog_product_view_id_2');

        $buildModel = function (array $handles) use ($objectManager, $theme): Merge {
            $model = $objectManager->create(
                \Magento\Framework\View\Model\Layout\Merge::class,
                ['theme' => $theme]
            );
            $model->addHandle($handles);
            return $model;
        };

        $productA = $buildModel(['default', 'catalog_product_view', 'catalog_product_view_id_1']);
        $productB = $buildModel(['default', 'catalog_product_view', 'catalog_product_view_id_2']);

        $this->assertNotSame($productA->getCacheId(), $productB->getCacheId());
    }

    public function testLoadDbApp()
    {
        $this->assertEmpty($this->model->getHandles());
        $this->assertEmpty($this->model->asString());
        $handles = ['fixture_handle_one', 'fixture_handle_two'];
        $this->model->load($handles);
        $expectedResult = '
            <root>
                <body>
                    <block class="Magento\Framework\View\Element\Template"
                           template="Magento_Framework::fixture_template_one.phtml"/>
                </body>
                <body>
                    <block class="Magento\Framework\View\Element\Template" 
                           template="Magento_Framework::fixture_template_two.phtml"/>
                </body>
            </root>
        ';
        $actualResult = '<root>' . $this->model->asString() . '</root>';
        $this->assertXmlStringEqualsXmlString($expectedResult, $actualResult);
    }
}
