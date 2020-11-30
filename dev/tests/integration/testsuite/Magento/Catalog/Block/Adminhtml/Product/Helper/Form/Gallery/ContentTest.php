<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class ContentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test subject.
     *
     * @var Content
     */
    private $block;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $gallery = Bootstrap::getObjectManager()->get(Gallery::class);
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        $this->block = $layout->createBlock(
            Content::class,
            'block'
        );
        $this->block->setElement($gallery);
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->dataPersistor = Bootstrap::getObjectManager()->get(DataPersistorInterface::class);
    }

    public function testGetUploader()
    {
        $this->assertInstanceOf(\Magento\Backend\Block\Media\Uploader::class, $this->block->getUploader());
    }

    /**
     * Test get images json using registry or data persistor.
     *
     * @dataProvider getImagesAndImageTypesDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoAppIsolation enabled
     * @param bool $isProductNew
     * @return void
     */
    public function testGetImagesJson(bool $isProductNew)
    {
        $this->prepareProduct($isProductNew);
        $imagesJson = $this->block->getImagesJson();
        $images = json_decode($imagesJson);
        $image = array_shift($images);
        $this->assertMatchesRegularExpression('~/m/a/magento_image~', $image->file);
        $this->assertSame('image', $image->media_type);
        $this->assertSame('Image Alt Text', $image->label);
        $this->assertSame('Image Alt Text', $image->label_default);
        $this->assertMatchesRegularExpression('~/media/catalog/product/m/a/magento_image~', $image->url);
    }

    /**
     * Test get image types json using registry or data persistor.
     *
     * @dataProvider getImagesAndImageTypesDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     * @magentoAppIsolation enabled
     * @param bool $isProductNew
     * @return void
     */
    public function testGetImageTypes(bool $isProductNew)
    {
        $this->prepareProduct($isProductNew);
        $imageTypes = $this->block->getImageTypes();
        foreach ($imageTypes as $type => $image) {
            $this->assertSame($type, $image['code']);
            $type !== 'swatch_image'
                ? $this->assertMatchesRegularExpression('/\/m\/a\/magento_image/', $image['value'])
                : $this->assertNull($image['value']);
            $this->assertSame('[STORE VIEW]', $image['scope']->getText());
            $this->assertSame(sprintf('product[%s]', $type), $image['name']);
        }
    }

    /**
     * Provide test data for testGetImagesJson() and tesGetImageTypes().
     *
     * @return array
     */
    public function getImagesAndImageTypesDataProvider()
    {
        return [
            [
                'isProductNew' => true,
            ],
            [
                'isProductNew' => false,
            ],
        ];
    }

    /**
     * Prepare product, and set it to registry and data persistor.
     *
     * @param bool $isProductNew
     * @return void
     */
    private function prepareProduct(bool $isProductNew)
    {
        $product = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class)->get('simple');
        if ($isProductNew) {
            $newProduct = Bootstrap::getObjectManager()->create(Product::class);
            $this->registry->register('current_product', $newProduct);
            $productData['product'] = $product->getData();
            $dataPersistor = Bootstrap::getObjectManager()->get(DataPersistorInterface::class);
            $dataPersistor->set('catalog_product', $productData);
        } else {
            $this->registry->register('current_product', $product);
        }
    }
}
