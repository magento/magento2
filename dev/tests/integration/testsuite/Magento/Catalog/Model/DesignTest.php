<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Result\Page;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Model\Theme;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Catalog\Model\Design.
 */
class DesignTest extends TestCase
{
    /**
     * @var Design
     */
    private $model;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->create(Design::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * @dataProvider getThemeModel
     * @param Theme $theme
     * @return void
     */
    public function testApplyCustomDesign(Theme $theme): void
    {
        $this->model->applyCustomDesign($theme);
        $design = Bootstrap::getObjectManager()->get(DesignInterface::class);
        $translate = Bootstrap::getObjectManager()->get(TranslateInterface::class);
        $this->assertEquals('package', $design->getDesignTheme()->getPackageCode());
        $this->assertEquals('theme', $design->getDesignTheme()->getThemeCode());
        $this->assertEquals('themepackage/theme', $translate->getTheme());
    }

    /**
     * Verify design product settings will be generated correctly for PDP.
     *
     * @magentoDataFixture Magento/Catalog/_files/simple_product_with_custom_design.php
     * @param array $designSettings
     * @param array $expectedSetting
     * @dataProvider getDesignSettingsForProductWithScheduleDesignTest
     * @return void
     */
    public function testGetDesignSettingsForProductWithScheduleDesign(
        array $designSettings,
        array $expectedSetting
    ): void {
        $product = $this->productRepository->get('simple_with_custom_design', false, null, true);
        $this->applyScheduleDesignUpdate($product, $designSettings);
        $settings = $this->model->getDesignSettings($product);
        self::assertEquals($expectedSetting['page_layout'], $settings->getData('page_layout'));
        self::assertEquals($expectedSetting['custom_design'], $settings->getData('custom_design'));
    }

    /**
     * @return array[]
     */
    public static function getDesignSettingsForProductWithScheduleDesignTest(): array
    {
        $datetime = new \DateTime();
        $datetime->modify('-10 day');
        $fromApplied = $datetime->format('Y-m-d');
        $datetime->modify('+20 day');
        $fromNotApplied = $datetime->format('Y-m-d');
        $datetime->modify('+30 day');
        $to = $datetime->format('Y-m-d');

        return [
            'schedule_design_applied' => [
                'designSettings' => [
                    'custom_layout' => '2columns-left',
                    'custom_design' => '2',
                    'custom_design_from' => $fromApplied,
                    'custom_design_to' => $to,
                ],
                'expectedSetting' => [
                    'page_layout' => '2columns-left',
                    'custom_design' => '2',
                ]
            ],
            'schedule_design_not_applied' => [
                'designSettings' => [
                    'custom_layout' => '2columns-left',
                    'custom_design' => '2',
                    'custom_design_from' => $fromNotApplied,
                    'custom_design_to' => $to,
                ],
                'expectedSetting' => [
                    'page_layout' => '3columns',
                    'custom_design' => null,
                ]
            ],
        ];
    }

    /**
     * Verify ability to add category inherited page layout handles for product.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testAddCategoryPageLayoutHandlesForProduct(): void
    {
        $resultPage = Bootstrap::getObjectManager()->create(Page::class);
        $product = $this->productRepository->get('simple');
        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getParentDesignCategory'])
            ->getMock();
        $category->expects(self::once())->method('getParentDesignCategory')->willReturnSelf();
        $category->setId(1);
        $category->setCustomLayoutUpdateFile('testFile');
        $category->setCustomApplyToProducts(1);
        $product->setCategory($category);
        $settings = $this->model->getDesignSettings($product);
        $resultPage->addPageLayoutHandles($settings->getPageLayoutHandles());
        $handles = $resultPage->getLayout()->getUpdate()->getHandles();
        self::assertEquals(
            'catalog_category_view_selectable_1_testFile',
            $handles[1]
        );
    }

    /**
     * @return array
     */
    public static function getThemeModel(): array
    {
        $theme = Bootstrap::getObjectManager()->create(ThemeInterface::class);
        $theme->setData(self::_getThemeData());

        return [[$theme]];
    }

    /**
     * @return array
     */
    protected static function _getThemeData()
    {
        return [
            'theme_title' => 'Magento Theme',
            'theme_code' => 'theme',
            'package_code' => 'package',
            'theme_path' => 'package/theme',
            'parent_theme' => null,
            'is_featured' => true,
            'preview_image' => '',
            'theme_directory' => __DIR__ . '_files/design/frontend/default/default',
        ];
    }

    /**
     * Apply provided setting to product scheduled design update.
     *
     * @param ProductInterface $product
     * @param array $designSettings
     * @return void
     */
    private function applyScheduleDesignUpdate(ProductInterface $product, array $designSettings): void
    {
        $product->setCustomLayout($designSettings['custom_layout']);
        $product->setCustomDesign($designSettings['custom_design']);
        $product->setCustomDesignFrom($designSettings['custom_design_from']);
        $product->setCustomDesignTo($designSettings['custom_design_to']);
    }
}
