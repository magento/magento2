<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Block\Adminhtml\Widget;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Model\ResourceModel\Theme as ThemeResource;
use Magento\Theme\Model\ThemeFactory;
use PHPUnit\Framework\TestCase;

/**
 * Checks widget grid filtering and sorting
 *
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class InstanceTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var PageFactory */
    private $pageFactory;

    /** @var RequestInterface */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->request = $this->objectManager->get(RequestInterface::class);
        $this->pageFactory = $this->objectManager->get(PageFactory::class);
    }

    /**
     * @dataProvider gridFiltersDataProvider
     *
     * @magentoDataFixture Magento/Widget/_files/widgets.php
     *
     * @param array $filter
     * @param array $expectedWidgets
     * @return void
     */
    public function testGridFiltering(array $filter, array $expectedWidgets): void
    {
        $this->request->setParams($filter);
        $collection = $this->getGridCollection();

        $this->assertWidgets($expectedWidgets, $collection);
    }

    /**
     * @return array
     */
    public static function gridFiltersDataProvider(): array
    {
        return [
            'first_page' => [
                'filter' => [
                    'limit' => 2,
                    'page' => 1,
                ],
                'expectedWidgets' => [
                    'cms page widget title',
                    'product link widget title',
                ],
            ],
            'second_page' => [
                'filter' => [
                    'limit' => 2,
                    'page' => 2,
                ],
                'expectedWidgets' => [
                    'recently compared products',
                ],
            ],
            'filter_by_title' => [
                'filter' => [
                    'filter' => base64_encode('title=product link widget title'),
                ],
                'expectedWidgets' => [
                    'product link widget title',
                ],
            ],
            'filter_by_type' => [
                'filter' => [
                    'filter' => base64_encode('type=Magento%5CCms%5CBlock%5CWidget%5CPage%5CLink'),
                ],
                'expectedWidgets' => [
                    'cms page widget title',
                ],
            ],
            'filter_by_theme' => [
                'filter' => [
                    'filter' => base64_encode('theme_id=' . self::loadThemeIdByCode('Magento/blank')),
                ],
                'expectedWidgets' => [
                    'recently compared products',
                ],
            ],
            'filter_by_sort_order' => [
                'filter' => [
                    'filter' => base64_encode('sort_order=1'),
                ],
                'expectedWidgets' => [
                    'recently compared products',
                ],
            ],
            'filter_by_title_and_luma_theme' => [
                'filter' => [
                    'filter' => base64_encode(
                        'title=cms page widget title&theme_id=' . self::loadThemeIdByCode('Magento/luma')
                    ),
                ],
                'expectedWidgets' => [
                    'cms page widget title',
                ],
            ],
            'filter_by_title_and_blank_theme' => [
                'filter' => [
                    'filter' => base64_encode(
                        'title=recently compared products&theme_id=' . self::loadThemeIdByCode('Magento/blank')
                    ),
                ],
                'expectedWidgets' => [
                    'recently compared products',
                ],
            ],
        ];
    }

    /**
     * @dataProvider gridSortDataProvider
     *
     * @magentoDataFixture Magento/Widget/_files/widgets.php
     *
     * @param array $filter
     * @param array $expectedWidgets
     * @return void
     */
    public function testGridSorting(array $filter, array $expectedWidgets): void
    {
        $this->request->setParams($filter);
        $collection = $this->getGridCollection();
        $this->assertEquals($expectedWidgets, $collection->getColumnValues('title'));
    }

    /**
     * @return array
     */
    public static function gridSortDataProvider(): array
    {
        return [
            'sort_by_id_asc' => [
                'filter' => ['sort' => 'instance_id', 'dir' => 'asc'],
                'expectedWidgets' => [
                    'cms page widget title',
                    'product link widget title',
                    'recently compared products',
                ],
            ],
            'sort_by_id_desc' => [
                'filter' => ['sort' => 'instance_id', 'dir' => 'desc'],
                'expectedWidgets' => [
                    'recently compared products',
                    'product link widget title',
                    'cms page widget title',
                ],
            ],
            'sort_by_title_asc' => [
                'filter' => ['sort' => 'title', 'dir' => 'asc'],
                'expectedWidgets' => [
                    'cms page widget title',
                    'product link widget title',
                    'recently compared products',
                ],
            ],
            'sort_by_title_desc' => [
                'filter' => ['sort' => 'title', 'dir' => 'desc'],
                'expectedWidgets' => [
                    'recently compared products',
                    'product link widget title',
                    'cms page widget title',
                ],
            ],
            'sort_by_type_asc' => [
                'filter' => ['sort' => 'type', 'dir' => 'asc'],
                'expectedWidgets' => [
                    'product link widget title',
                    'recently compared products',
                    'cms page widget title',
                ],
            ],
            'sort_by_type_desc' => [
                'filter' => ['sort' => 'type', 'dir' => 'desc'],
                'expectedWidgets' => [
                    'cms page widget title',
                    'recently compared products',
                    'product link widget title',
                ],
            ],
            'sort_by_sort_order_asc' => [
                'filter' => ['sort' => 'sort_order', 'dir' => 'asc'],
                'expectedWidgets' => [
                    'recently compared products',
                    'product link widget title',
                    'cms page widget title',
                ],
            ],
            'sort_by_sort_order_desc' => [
                'filter' => ['sort' => 'sort_order', 'dir' => 'desc'],
                'expectedWidgets' => [
                    'cms page widget title',
                    'product link widget title',
                    'recently compared products',
                ],
            ],
            'sort_by_theme_asc' => [
                'filter' => ['sort' => 'theme_id', 'dir' => 'asc'],
                'expectedWidgets' => [
                    'recently compared products',
                    'cms page widget title',
                    'product link widget title',
                ],
            ],
            'sort_by_theme_desc' => [
                'filter' => ['sort' => 'theme_id', 'dir' => 'asc'],
                'expectedWidgets' => [
                    'recently compared products',
                    'cms page widget title',
                    'product link widget title',
                ],
            ],
        ];
    }

    /**
     * Load theme by theme id
     *
     * @param string $code
     * @return int
     */
    private static function loadThemeIdByCode(string $code): int
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ThemeFactory $themeFactory */
        $themeFactory = $objectManager->get(ThemeFactory::class);
        /** @var ThemeResource $themeResource */
        $themeResource = $objectManager->get(ThemeResource::class);
        $theme = $themeFactory->create();
        $themeResource->load($theme, $code, 'code');

        return (int)$theme->getId();
    }

    /**
     * Assert widget instances
     *
     * @param $expectedWidgets
     * @param AbstractCollection $collection
     * @return void
     */
    private function assertWidgets($expectedWidgets, AbstractCollection $collection): void
    {
        $this->assertCount(count($expectedWidgets), $collection);
        foreach ($expectedWidgets as $widgetTitle) {
            $item = $collection->getItemByColumnValue('title', $widgetTitle);
            $this->assertNotNull($item, sprintf('Expected widget %s is not present in grid', $widgetTitle));
        }
    }

    /**
     * Prepare page layout
     *
     * @return LayoutInterface
     */
    private function preparePageLayout(): LayoutInterface
    {
        $page = $this->pageFactory->create();
        $page->addHandle([
            'default',
            'adminhtml_widget_instance_index',
        ]);

        return $page->getLayout()->generateXml();
    }

    /**
     * Get prepared grid collection
     *
     * @return AbstractCollection
     */
    private function getGridCollection(): AbstractCollection
    {
        $layout = $this->preparePageLayout();
        $containerBlock = $layout->getBlock('adminhtml.widget.instance.grid.container');
        $grid = $containerBlock->getChildBlock('grid');
        $this->assertNotFalse($grid);

        return $grid->getPreparedCollection();
    }
}
