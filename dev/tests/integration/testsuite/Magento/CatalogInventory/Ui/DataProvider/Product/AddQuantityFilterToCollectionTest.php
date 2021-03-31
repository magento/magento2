<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Ui\DataProvider\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks that the product quantity filter is working correctly
 *
 * @magentoAppArea adminhtml
 */
class AddQuantityFilterToCollectionTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var UiComponentFactory */
    private $componentFactory;

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
        $this->componentFactory = $this->objectManager->get(UiComponentFactory::class);
    }

    /**
     * @dataProvider quantityFilterProvider
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @param array $filter
     * @param array $expectedProducts
     * @return void
     */
    public function testQuantityFilter(array $filter, array $expectedProducts): void
    {
        $this->request->setParams([ContextInterface::FILTER_VAR => $filter]);
        $dataProviderData = $this->getComponentProvidedData('product_listing');
        $actualProducts = array_column($dataProviderData['items'], 'sku');
        $this->assertEquals($expectedProducts, $actualProducts, 'Expected products do not match actual products!');
    }

    /**
     * Data provider for testQuantityFilter
     *
     * @return array
     */
    public function quantityFilterProvider(): array
    {
        return [
            'from' => [
                'filter' => [
                    'qty' => [
                        'from' => 100,
                    ],
                ],
                'expected_products' => [
                    'simple1',
                    'simple3',
                ],
            ],
            'to' => [
                'filter' => [
                    'qty' => [
                        'to' => 100,
                    ],
                ],
                'expected_products' => [
                    'simple1',
                    'simple2',
                ],
            ],
            'both' => [
                'filter' => [
                    'qty' => [
                        'from' => 60,
                        'to' => 130,
                    ],
                ],
                'expected_products' => [
                    'simple1',
                ],
            ],
        ];
    }

    /**
     * Call prepare method in the child components
     *
     * @param UiComponentInterface $component
     * @return void
     */
    private function prepareChildComponents(UiComponentInterface $component): void
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareChildComponents($child);
        }

        $component->prepare();
    }

    /**
     * Get component provided data
     *
     * @param string $namespace
     * @return array
     */
    private function getComponentProvidedData(string $namespace): array
    {
        $component = $this->componentFactory->create($namespace);
        $this->prepareChildComponents($component);

        return $component->getContext()->getDataProvider()->getData();
    }
}
