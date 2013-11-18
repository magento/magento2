<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model;

/**
 * Tests product model:
 * - general behaviour is tested (external interaction and pricing is not tested there)
 *
 * @see \Magento\Catalog\Model\ProductExternalTest
 * @see \Magento\Catalog\Model\ProductPriceTest
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product');
    }

    public static function tearDownAfterClass()
    {
        /** @var \Magento\Catalog\Model\Product\Media\Config $config */
        $config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Catalog\Model\Product\Media\Config');

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Filesystem');
        $filesystem->delete($config->getBaseMediaPath());
        $filesystem->delete($config->getBaseTmpMediaPath());
    }

    public function testCanAffectOptions()
    {
        $this->assertFalse($this->_model->canAffectOptions());
        $this->_model->canAffectOptions(true);
        $this->assertTrue($this->_model->canAffectOptions());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testCRUD()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\StoreManagerInterface')
            ->setCurrentStore(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                    ->get('Magento\Core\Model\StoreManagerInterface')
                    ->getStore(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID)
            );
        $this->_model->setTypeId('simple')->setAttributeSetId(4)
            ->setName('Simple Product')->setSku(uniqid())->setPrice(10)
            ->setMetaTitle('meta title')->setMetaKeyword('meta keyword')->setMetaDescription('meta description')
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Status::STATUS_ENABLED)
        ;
        $crud = new \Magento\TestFramework\Entity($this->_model, array('sku' => uniqid()));
        $crud->testCrud();
    }

    public function testCleanCache()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')
            ->saveCache('test', 'catalog_product_999', array('catalog_product_999'));
        // potential bug: it cleans by cache tags, generated from its ID, which doesn't make much sense
        $this->_model->setId(999)->cleanCache();
        $this->assertEmpty(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')
                ->loadCache('catalog_product_999')
        );
    }

    public function testAddImageToMediaGallery()
    {
        // Model accepts only files in tmp media path, we need to copy fixture file there
        $mediaFile = $this->_copyFileToBaseTmpMediaPath(dirname(__DIR__) . '/_files/magento_image.jpg');

        $this->_model->addImageToMediaGallery($mediaFile);
        $gallery = $this->_model->getData('media_gallery');
        $this->assertNotEmpty($gallery);
        $this->assertTrue(isset($gallery['images'][0]['file']));
        $this->assertStringStartsWith('/m/a/magento_image', $gallery['images'][0]['file']);
        $this->assertTrue(isset($gallery['images'][0]['position']));
        $this->assertTrue(isset($gallery['images'][0]['disabled']));
        $this->assertArrayHasKey('label', $gallery['images'][0]);
    }

    /**
     * Copy file to media tmp directory and return it's name
     *
     * @param string $sourceFile
     * @return string
     */
    protected function _copyFileToBaseTmpMediaPath($sourceFile)
    {
        /** @var \Magento\Catalog\Model\Product\Media\Config $config */
        $config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Catalog\Model\Product\Media\Config');
        $baseTmpMediaPath = $config->getBaseTmpMediaPath();

        $targetFile = $baseTmpMediaPath . DS . basename($sourceFile);

        /** @var \Magento\Filesystem $filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Filesystem');
        $filesystem->setIsAllowCreateDirectories(true);
        $filesystem->copy($sourceFile, $targetFile);

        return $targetFile;
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testDuplicate()
    {
        $this->_model->load(1); // fixture
        $duplicate = $this->_model->duplicate();
        try {
            $this->assertNotEmpty($duplicate->getId());
            $this->assertNotEquals($duplicate->getId(), $this->_model->getId());
            $this->assertNotEquals($duplicate->getSku(), $this->_model->getSku());
            $this->assertEquals(\Magento\Catalog\Model\Product\Status::STATUS_DISABLED, $duplicate->getStatus());
            $this->_undo($duplicate);
        } catch (\Exception $e) {
            $this->_undo($duplicate);
            throw $e;
        }
    }

    public function testDuplicateSkuGeneration()
    {
        $this->_model->load(1);
        $this->assertEquals('simple', $this->_model->getSku());
        $duplicated = $this->_model->duplicate();
        $this->assertEquals('simple-1', $duplicated->getSku());
    }

    /**
     * Delete model
     *
     * @param \Magento\Core\Model\AbstractModel $duplicate
     */
    protected function _undo($duplicate)
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\StoreManagerInterface')
            ->getStore()->setId(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID);
        $duplicate->delete();
    }

    /**
     * @covers \Magento\Catalog\Model\Product::isGrouped
     * @covers \Magento\Catalog\Model\Product::isSuperGroup
     * @covers \Magento\Catalog\Model\Product::isSuper
     */
    public function testIsGrouped()
    {
        $this->assertFalse($this->_model->isGrouped());
        $this->assertFalse($this->_model->isSuperGroup());
        $this->assertFalse($this->_model->isSuper());
        $this->_model->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_GROUPED);
        $this->assertTrue($this->_model->isGrouped());
        $this->assertTrue($this->_model->isSuperGroup());
        $this->assertTrue($this->_model->isSuper());
    }

    /**
     * @covers \Magento\Catalog\Model\Product::isConfigurable
     * @covers \Magento\Catalog\Model\Product::isSuperConfig
     * @covers \Magento\Catalog\Model\Product::isSuper
     */
    public function testIsConfigurable()
    {
        $this->assertFalse($this->_model->isConfigurable());
        $this->assertFalse($this->_model->isSuperConfig());
        $this->assertFalse($this->_model->isSuper());
        $this->_model->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_CONFIGURABLE);
        $this->assertTrue($this->_model->isConfigurable());
        $this->assertTrue($this->_model->isSuperConfig());
        $this->assertTrue($this->_model->isSuper());
    }

    /**
     * @covers \Magento\Catalog\Model\Product::getVisibleInCatalogStatuses
     * @covers \Magento\Catalog\Model\Product::getVisibleStatuses
     * @covers \Magento\Catalog\Model\Product::isVisibleInCatalog
     * @covers \Magento\Catalog\Model\Product::getVisibleInSiteVisibilities
     * @covers \Magento\Catalog\Model\Product::isVisibleInSiteVisibility
     */
    public function testVisibilityApi()
    {
        $this->assertEquals(
            array(\Magento\Catalog\Model\Product\Status::STATUS_ENABLED), $this->_model->getVisibleInCatalogStatuses()
        );
        $this->assertEquals(
            array(\Magento\Catalog\Model\Product\Status::STATUS_ENABLED), $this->_model->getVisibleStatuses()
        );

        $this->_model->setStatus(\Magento\Catalog\Model\Product\Status::STATUS_DISABLED);
        $this->assertFalse($this->_model->isVisibleInCatalog());

        $this->_model->setStatus(\Magento\Catalog\Model\Product\Status::STATUS_ENABLED);
        $this->assertTrue($this->_model->isVisibleInCatalog());

        $this->assertEquals(array(
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
            ), $this->_model->getVisibleInSiteVisibilities()
        );

        $this->assertFalse($this->_model->isVisibleInSiteVisibility());
        $this->_model->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH);
        $this->assertTrue($this->_model->isVisibleInSiteVisibility());
        $this->_model->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG);
        $this->assertTrue($this->_model->isVisibleInSiteVisibility());
        $this->_model->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $this->assertTrue($this->_model->isVisibleInSiteVisibility());
    }

    /**
     * @covers \Magento\Catalog\Model\Product::isDuplicable
     * @covers \Magento\Catalog\Model\Product::setIsDuplicable
     */
    public function testIsDuplicable()
    {
        $this->assertTrue($this->_model->isDuplicable());
        $this->_model->setIsDuplicable(0);
        $this->assertFalse($this->_model->isDuplicable());
    }

    /**
     * @covers \Magento\Catalog\Model\Product::isSalable
     * @covers \Magento\Catalog\Model\Product::isSaleable
     * @covers \Magento\Catalog\Model\Product::isAvailable
     * @covers \Magento\Catalog\Model\Product::isInStock
     */
    public function testIsSalable()
    {
        $this->_model->load(1); // fixture
        $this->assertTrue((bool)$this->_model->isSalable());
        $this->assertTrue((bool)$this->_model->isSaleable());
        $this->assertTrue((bool)$this->_model->isAvailable());
        $this->assertTrue($this->_model->isInStock());
        $this->_model->setStatus(0);
        $this->assertFalse((bool)$this->_model->isSalable());
        $this->assertFalse((bool)$this->_model->isSaleable());
        $this->assertFalse((bool)$this->_model->isAvailable());
        $this->assertFalse($this->_model->isInStock());
    }

    /**
     * @covers \Magento\Catalog\Model\Product::isVirtual
     * @covers \Magento\Catalog\Model\Product::getIsVirtual
     */
    public function testIsVirtual()
    {
        $this->assertFalse($this->_model->isVirtual());
        $this->assertFalse($this->_model->getIsVirtual());

        /** @var $model \Magento\Catalog\Model\Product */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product',
            array('data' => array('type_id' => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL))
        );
        $this->assertTrue($model->isVirtual());
        $this->assertTrue($model->getIsVirtual());
    }

    public function testIsRecurring()
    {
        $this->assertFalse($this->_model->isRecurring());
        $this->_model->setIsRecurring(1);
        $this->assertTrue($this->_model->isRecurring());
    }

    public function testToArray()
    {
        $this->assertEquals(array(), $this->_model->toArray());
        $this->_model->setSku('sku')->setName('name');
        $this->assertEquals(array('sku' => 'sku', 'name' => 'name'), $this->_model->toArray());
    }

    public function testFromArray()
    {
        $this->_model->fromArray(array('sku' => 'sku', 'name' => 'name', 'stock_item' => array('key' => 'value')));
        $this->assertEquals(array('sku' => 'sku', 'name' => 'name'), $this->_model->getData());
    }

    public function testIsComposite()
    {
        $this->assertFalse($this->_model->isComposite());

        /** @var $model \Magento\Catalog\Model\Product */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product',
            array('data' => array('type_id' => \Magento\Catalog\Model\Product\Type::TYPE_CONFIGURABLE))
        );
        $this->assertTrue($model->isComposite());
    }

    /**
     * @param bool $isUserDefined
     * @param string $code
     * @param bool $expectedResult
     * @dataProvider isReservedAttributeDataProvider
     */
    public function testIsReservedAttribute($isUserDefined, $code, $expectedResult)
    {
        $attribute = new \Magento\Object(array('is_user_defined' => $isUserDefined, 'attribute_code' => $code));
        $this->assertEquals($expectedResult, $this->_model->isReservedAttribute($attribute));
    }

    public function isReservedAttributeDataProvider()
    {
        return array(
            array(true, 'position', true),
            array(true, 'type_id', false),
            array(false, 'no_difference', false)
        );
    }

    public function testSetOrigData()
    {
        $this->assertEmpty($this->_model->getOrigData());
        $this->_model->setOrigData('key', 'value');
        $this->assertEmpty($this->_model->getOrigData());

        $storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getId();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\StoreManagerInterface')
            ->getStore()->setId(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID);
        try {
            $this->_model->setOrigData('key', 'value');
            $this->assertEquals('value', $this->_model->getOrigData('key'));
        } catch (\Exception $e) {
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\StoreManagerInterface')
                ->getStore()->setId($storeId);
            throw $e;
        }
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\StoreManagerInterface')
            ->getStore()->setId($storeId);
    }

    public function testReset()
    {
        $model = $this->_model;

        $this->_assertEmpty($model);

        $this->_model->setData('key', 'value');
        $this->_model->reset();
        $this->_assertEmpty($model);

        $this->_model->setOrigData('key', 'value');
        $this->_model->reset();
        $this->_assertEmpty($model);

        $this->_model->addCustomOption('key', 'value');
        $this->_model->reset();
        $this->_assertEmpty($model);

        $this->_model->addOption(\Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product\Option'));
        $this->_model->reset();
        $this->_assertEmpty($model);

        $this->_model->canAffectOptions(true);
        $this->_model->reset();
        $this->_assertEmpty($model);
    }

    /**
     * Check is model empty or not
     *
     * @param \Magento\Core\Model\AbstractModel $model
     */
    protected function _assertEmpty($model)
    {
        $this->assertEquals(array(), $model->getData());
        $this->assertEquals(null, $model->getOrigData());
        $this->assertEquals(array(), $model->getCustomOptions());
        // impossible to test $_optionInstance
        $this->assertEquals(array(), $model->getOptions());
        $this->assertFalse($model->canAffectOptions());
        // impossible to test $_errors
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testIsProductsHasSku()
    {
        $this->assertTrue($this->_model->isProductsHasSku(array(10, 11)));
    }

    public function testProcessBuyRequest()
    {
        $request = new \Magento\Object;
        $result = $this->_model->processBuyRequest($request);
        $this->assertInstanceOf('Magento\Object', $result);
        $this->assertArrayHasKey('errors', $result->getData());
    }

    public function testValidate()
    {
        $this->_model->setTypeId('simple')->setAttributeSetId(4)->setName('Simple Product')
            ->setSku(uniqid('', true) . uniqid('', true) . uniqid('', true))->setPrice(10)->setMetaTitle('meta title')
            ->setMetaKeyword('meta keyword')->setMetaDescription('meta description')
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Status::STATUS_ENABLED)
            ->setCollectExceptionMessages(true)
        ;
        $validationResult = $this->_model->validate();
        $this->assertEquals('SKU length should be 64 characters maximum.', $validationResult['sku']);
        unset($validationResult['sku']);
        foreach ($validationResult as $error) {
            $this->assertTrue($error);
        }
    }
}
