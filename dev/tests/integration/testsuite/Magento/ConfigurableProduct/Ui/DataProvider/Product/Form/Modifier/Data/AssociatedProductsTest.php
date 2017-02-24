<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data;

class AssociatedProductsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Registry $registry
     */
    private $registry;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);

    }

    /**
     * @dataProvider getProductMatrixDataProvider
     * @param string $interfaceLocale
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoAppArea adminhtml
     */
    public function testGetProductMatrix($interfaceLocale)
    {
        $productSku = 'configurable';
        $associatedProductsData = [
            [10 => '10.000'],
            [20 => '20.000']
        ];
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->registry->register('current_product', $productRepository->get($productSku));
        /** @var $store \Magento\Store\Model\Store */
        $store = $this->objectManager->create(\Magento\Store\Model\Store::class);
        $store->load('admin');
        $this->registry->register('current_store', $store);
        /** @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject $localeResolver */
        $localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->setMethods(['getLocale'])
            ->getMockForAbstractClass();
        $localeResolver->expects($this->any())->method('getLocale')->willReturn($interfaceLocale);
        $localeCurrency = $this->objectManager->create(
            \Magento\Framework\Locale\CurrencyInterface::class,
            ['localeResolver' => $localeResolver]
        );
        $associatedProducts = $this->objectManager->create(
            AssociatedProducts::class,
            ['localeCurrency' => $localeCurrency]
        );
        foreach ($associatedProducts->getProductMatrix() as $productMatrixId => $productMatrixData) {
            $this->assertEquals(
                $associatedProductsData[$productMatrixId][$productMatrixData['id']],
                $productMatrixData['price']
            );
        }
    }

    /**
     * @return array
     */
    public function getProductMatrixDataProvider()
    {
        return [
            ['en_US'],
            ['zh_Hans_CN']
        ];
    }
}
