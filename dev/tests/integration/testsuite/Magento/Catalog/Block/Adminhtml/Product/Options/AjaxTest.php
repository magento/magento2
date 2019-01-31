<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Options;

/**
 * @magentoAppArea adminhtml
 */
class AjaxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Options\Ajax
     */
    protected $_block = null;

    protected function setUp()
    {
        parent::setUp();
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Catalog\Block\Adminhtml\Product\Options\Ajax::class
        );
    }

    public function testToHtmlWithoutProducts()
    {
        $this->assertEquals(json_encode([]), $this->_block->toHtml());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_with_options.php
     */
    public function testToHtml()
    {
        /** @var \Magento\TestFramework\ObjectManager $objectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

        $objectManager->get(\Magento\Framework\Registry::class)
            ->register(
                'import_option_products',
                [$productRepository->get('simple')->getId()]
            );

        $result = json_decode($this->_block->toHtml(), true);

        $this->assertEquals('test_option_code_1', $result[0]['title']);
    }
}
