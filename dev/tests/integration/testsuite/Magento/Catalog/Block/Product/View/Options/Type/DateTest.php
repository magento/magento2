<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View\Options\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DateTest extends TestCase
{
    /**
     * @var Date
     */
    private $block;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->productHelper = $objectManager->get(ProductHelper::class);
        $this->dataObjectFactory = $objectManager->get(DataObjectFactory::class);
        $layout = $objectManager->get(LayoutInterface::class);
        $this->localeResolver = $objectManager->get(ResolverInterface::class);
        $this->defaultLocale = $this->localeResolver->getLocale();
        $this->block = $layout->createBlock(
            Date::class,
            'product.info.options.date',
            [
                'data' => [
                    'template' => 'Magento_Catalog::product/view/options/type/date.phtml'
                ]
            ]
        );
        $layout->createBlock(
            Render::class,
            'product.price.render.default',
            [
                'data' => [
                    'price_render_handle' => 'catalog_product_prices',
                    'use_link_for_as_low_as' => true,
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->localeResolver->setLocale($this->defaultLocale);
        parent::tearDown();
    }

    /**
     * @param array $data
     * @param array $expected
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store catalog/custom_options/year_range 2020,2030
     * @dataProvider toHtmlWithDropDownDataProvider
     */
    public function testToHtmlWithDropDown(array $data, array $expected): void
    {
        $this->prepareBlock($data);
        $this->assertXPaths($expected);
    }

    /**
     * @param array $data
     * @param array $expected
     * @param string|null $locale
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store catalog/custom_options/use_calendar 1
     * @magentoConfigFixture current_store catalog/custom_options/year_range 2020,2030
     * @dataProvider toHtmlWithCalendarDataProvider
     */
    public function testToHtmlWithCalendar(array $data, array $expected, ?string $locale = null): void
    {
        if ($locale) {
            $this->localeResolver->setLocale($locale);
        }
        $this->prepareBlock($data);
        $this->assertXPaths($expected);
    }

    /**
     * @param array $expected
     */
    private function assertXPaths(array $expected): void
    {
        $html = $this->block->toHtml();
        $domXpath = new \DOMXPath($this->getHtmlDocument($html));
        foreach ($expected as $xpath => $value) {
            $xpath = strtr($xpath, ['{id}' => $this->block->getOption()->getId()]);
            $nodes = $domXpath->query(strtr($xpath, ['{id}' => $this->block->getOption()->getId()]));
            $this->assertEquals(1, $nodes->count(), 'Cannot find element \'' . $xpath . '"\' in the HTML');
            $this->assertEquals($value, $nodes->item(0)->getAttribute('value'));
        }
    }

    /**
     * @param array $data
     */
    private function prepareBlock(array $data): void
    {
        /** @var Product $product */
        $product = $this->productRepository->get('simple');
        $this->block->setProduct($product);
        $option = $this->getDateTimeOption($product);
        $this->block->setOption($option);
        $buyRequest = $this->dataObjectFactory->create();
        $buyRequest->setData(
            [
                'qty' => 1,
                'options' => [
                    $option->getId() => $data
                ],
            ]
        );
        $this->productHelper->prepareProductOptions($product, $buyRequest);
    }

    /**
     * @param Product $product
     * @return Option|null
     */
    private function getDateTimeOption(Product $product): ?Option
    {
        $option = null;
        foreach ($product->getOptions() as $customOption) {
            if ($customOption->getType() === Option::OPTION_TYPE_DATE_TIME) {
                $option = $customOption;
                break;
            }
        }
        return $option;
    }

    /**
     * @param string $source
     * @return \DOMDocument
     */
    private function getHtmlDocument(string $source): \DOMDocument
    {
        $page =<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
$source
</body>
</html>
HTML;
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $domDocument->loadHTML($page);
        libxml_use_internal_errors(false);
        return $domDocument;
    }

    /**
     * @return array
     */
    public function toHtmlWithDropDownDataProvider(): array
    {
        return [
            [
                [
                    'month' => '3',
                    'day' => '5',
                    'year' => '2020',
                    'hour' => '2',
                    'minute' => '15',
                    'day_part' => 'am',
                    'date_internal' => '2020-09-30 02:15:00'
                ],
                [
                    '//select[@id="options_{id}_year"]/option[@selected]' => '2020',
                    '//select[@id="options_{id}_month"]/option[@selected]' => '3',
                    '//select[@id="options_{id}_day"]/option[@selected]' => '5',
                    '//select[@id="options_{id}_hour"]/option[@selected]' => '2',
                    '//select[@id="options_{id}_minute"]/option[@selected]' => '15',
                    '//select[@id="options_{id}_day_part"]/option[@selected]' => 'am',
                ]
            ],
            [
                [
                    'date' => '09/30/2022',
                    'hour' => '2',
                    'minute' => '15',
                    'day_part' => 'am',
                    'date_internal' => '2020-09-30 02:15:00'
                ],
                [
                    '//select[@id="options_{id}_year"]/option[@selected]' => '2020',
                    '//select[@id="options_{id}_month"]/option[@selected]' => '9',
                    '//select[@id="options_{id}_day"]/option[@selected]' => '30',
                    '//select[@id="options_{id}_hour"]/option[@selected]' => '2',
                    '//select[@id="options_{id}_minute"]/option[@selected]' => '15',
                    '//select[@id="options_{id}_day_part"]/option[@selected]' => 'am',
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function toHtmlWithCalendarDataProvider(): array
    {
        return [
            [
                [
                    'month' => '3',
                    'day' => '5',
                    'year' => '2020',
                    'hour' => '2',
                    'minute' => '15',
                    'day_part' => 'am',
                    'date_internal' => '2020-09-30 02:15:00'
                ],
                [
                    '//input[@id="options_{id}_date"]' => '3/5/2020',
                    '//select[@id="options_{id}_hour"]/option[@selected]' => '2',
                    '//select[@id="options_{id}_minute"]/option[@selected]' => '15',
                    '//select[@id="options_{id}_day_part"]/option[@selected]' => 'am',
                ]
            ],
            [
                [
                    'date' => '09/30/2022',
                    'hour' => '2',
                    'minute' => '15',
                    'day_part' => 'am',
                    'date_internal' => '2020-09-30 02:15:00'
                ],
                [
                    '//input[@id="options_{id}_date"]' => '9/30/2020',
                    '//select[@id="options_{id}_hour"]/option[@selected]' => '2',
                    '//select[@id="options_{id}_minute"]/option[@selected]' => '15',
                    '//select[@id="options_{id}_day_part"]/option[@selected]' => 'am',
                ]
            ],
            [
                [
                    'month' => '3',
                    'day' => '5',
                    'year' => '2020',
                    'hour' => '2',
                    'minute' => '15',
                    'day_part' => 'am',
                    'date_internal' => '2020-09-30 02:15:00'
                ],
                [
                    '//input[@id="options_{id}_date"]' => '05/03/2020',
                    '//select[@id="options_{id}_hour"]/option[@selected]' => '2',
                    '//select[@id="options_{id}_minute"]/option[@selected]' => '15',
                    '//select[@id="options_{id}_day_part"]/option[@selected]' => 'am',
                ],
                'fr_FR'
            ],
            [
                [
                    'date' => '09/30/2022',
                    'hour' => '2',
                    'minute' => '15',
                    'day_part' => 'am',
                    'date_internal' => '2020-09-30 02:15:00'
                ],
                [
                    '//input[@id="options_{id}_date"]' => '30/09/2020',
                    '//select[@id="options_{id}_hour"]/option[@selected]' => '2',
                    '//select[@id="options_{id}_minute"]/option[@selected]' => '15',
                    '//select[@id="options_{id}_day_part"]/option[@selected]' => 'am',
                ],
                'fr_FR'
            ]
        ];
    }
}
