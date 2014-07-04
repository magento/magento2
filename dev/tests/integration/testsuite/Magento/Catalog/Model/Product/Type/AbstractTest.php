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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Product\Type;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Type\AbstractType
     */
    protected $_model;

    protected function setUp()
    {
        $productFactory = $this->getMock('Magento\Catalog\Model\ProductFactory', array(), array(), '', false);
        $catalogProductOption = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Product\Option'
        );
        $eavConfig = $this->getMock('Magento\Eav\Model\Config', array(), array(), '', false);
        $catalogProductType = $this->getMock('Magento\Catalog\Model\Product\Type', array(), array(), '', false);
        $eventManager = $this->getMock(
            'Magento\Framework\Event\ManagerInterface',
            array('dispatch'),
            array(),
            '',
            false
        );
        $coreData = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $fileStorageDb = $this->getMock('Magento\Core\Helper\File\Storage\Database', array(), array(), '', false);
        $filesystem = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $registry = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);
        $logger = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $this->_model = $this->getMockForAbstractClass(
            'Magento\Catalog\Model\Product\Type\AbstractType',
            array(
                $productFactory,
                $catalogProductOption,
                $eavConfig,
                $catalogProductType,
                $eventManager,
                $coreData,
                $fileStorageDb,
                $filesystem,
                $registry,
                $logger
            )
        );
    }

    public function testGetRelationInfo()
    {
        $info = $this->_model->getRelationInfo();
        $this->assertInstanceOf('Magento\Framework\Object', $info);
        $this->assertNotSame($info, $this->_model->getRelationInfo());
    }

    public function testGetChildrenIds()
    {
        $this->assertEquals(array(), $this->_model->getChildrenIds('value'));
    }

    public function testGetParentIdsByChild()
    {
        $this->assertEquals(array(), $this->_model->getParentIdsByChild('value'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetSetAttributes()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture
        $attributes = $this->_model->getSetAttributes($product);
        $this->assertArrayHasKey('sku', $attributes);
        $this->assertArrayHasKey('name', $attributes);
        foreach ($attributes as $attribute) {
            $this->assertInstanceOf('Magento\Catalog\Model\Resource\Eav\Attribute', $attribute);
        }
        /* possibility of fatal error if passing null instead of product */
    }

    public function testAttributesCompare()
    {
        $attribute[1] = new \Magento\Framework\Object(array('group_sort_path' => 1, 'sort_path' => 10));
        $attribute[2] = new \Magento\Framework\Object(array('group_sort_path' => 1, 'sort_path' => 5));
        $attribute[3] = new \Magento\Framework\Object(array('group_sort_path' => 2, 'sort_path' => 10));
        $this->assertEquals(1, $this->_model->attributesCompare($attribute[1], $attribute[2]));
        $this->assertEquals(-1, $this->_model->attributesCompare($attribute[2], $attribute[1]));
        $this->assertEquals(-1, $this->_model->attributesCompare($attribute[1], $attribute[3]));
        $this->assertEquals(1, $this->_model->attributesCompare($attribute[3], $attribute[1]));
        $this->assertEquals(-1, $this->_model->attributesCompare($attribute[2], $attribute[3]));
        $this->assertEquals(1, $this->_model->attributesCompare($attribute[3], $attribute[2]));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetEditableAttributes()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture
        $this->assertArrayNotHasKey('_cache_editable_attributes', $product->getData());
        $attributes = $this->_model->getEditableAttributes($product);
        $this->assertArrayHasKey('_cache_editable_attributes', $product->getData());

        // not clear how to test what is apply_to and what does it have to do with "editable" term

        $isTypeExists = false;
        foreach ($attributes as $attribute) {
            $this->assertInstanceOf('Magento\Catalog\Model\Resource\Eav\Attribute', $attribute);
            $applyTo = $attribute->getApplyTo();
            if (count($applyTo) > 0 && !in_array('simple', $applyTo)) {
                $isTypeExists = true;
            }
        }
        $this->assertTrue($isTypeExists);
    }

    public function testGetAttributeById()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        )->load(
            1
        );

        $this->assertNull($this->_model->getAttributeById(-1, $product));
        $this->assertNull($this->_model->getAttributeById(null, $product));

        $sku = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Eav\Model\Config'
        )->getAttribute(
            'catalog_product',
            'sku'
        );
        $this->assertSame($sku, $this->_model->getAttributeById($sku->getId(), $product));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testIsVirtual()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->assertFalse($this->_model->isVirtual($product));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testIsSalable()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->assertTrue($this->_model->isSalable($product));

        $product->load(1);
        // fixture
        $this->assertTrue((bool)$this->_model->isSalable($product));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * multiple_products.php because there are products without options, and they don't intersect
     * with product_simple.php by ID
     */
    public function testPrepareForCart()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(10);
        // fixture
        $this->assertEmpty($product->getCustomOption('info_buyRequest'));

        $requestData = array('qty' => 5);
        $result = $this->_model->prepareForCart(new \Magento\Framework\Object($requestData), $product);
        $this->assertArrayHasKey(0, $result);
        $this->assertSame($product, $result[0]);
        $buyRequest = $product->getCustomOption('info_buyRequest');
        $this->assertInstanceOf('Magento\Framework\Object', $buyRequest);
        $this->assertEquals($product->getId(), $buyRequest->getProductId());
        $this->assertSame($product, $buyRequest->getProduct());
        $this->assertEquals(serialize($requestData), $buyRequest->getValue());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testPrepareForCartOptionsException()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture
        $this->assertEquals(
            'Please specify the product\'s required option(s).',
            $this->_model->prepareForCart(new \Magento\Framework\Object(), $product)
        );
    }

    public function testGetSpecifyOptionMessage()
    {
        $this->assertEquals(
            'Please specify the product\'s required option(s).',
            $this->_model->getSpecifyOptionMessage()
        );
    }

    public function testCheckProductBuyState()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setSkipCheckRequiredOption('_');
        $this->_model->checkProductBuyState($product);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testCheckProductBuyStateException()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture
        $this->_model->checkProductBuyState($product);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetOrderOptions()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->assertEquals(array(), $this->_model->getOrderOptions($product));

        $product->load(1);
        // fixture
        $product->addCustomOption('info_buyRequest', serialize(new \Magento\Framework\Object(array('qty' => 2))));
        foreach ($product->getOptions() as $id => $option) {
            if ('field' == $option->getType()) {
                $product->addCustomOption('option_ids', $id);
                $quoteOption = clone $option;
                $product->addCustomOption("option_{$id}", $quoteOption->getValue());

                $optionArr = $this->_model->getOrderOptions($product);
                $this->assertArrayHasKey('info_buyRequest', $optionArr);
                $this->assertArrayHasKey('options', $optionArr);
                $this->assertArrayHasKey(0, $optionArr['options']);
                $renderedOption = $optionArr['options'][0];
                $this->assertArrayHasKey('label', $renderedOption);
                $this->assertArrayHasKey('value', $renderedOption);
                $this->assertArrayHasKey('print_value', $renderedOption);
                $this->assertArrayHasKey('option_id', $renderedOption);
                $this->assertArrayHasKey('option_type', $renderedOption);
                $this->assertArrayHasKey('option_value', $renderedOption);
                $this->assertArrayHasKey('custom_view', $renderedOption);
                $this->assertEquals($id, $renderedOption['option_id']);
                break;
            }
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testBeforeSave()
    {
        $this->markTestIncomplete('MAGETWO-9199');
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture
        $product->setData('links_purchased_separately', 'value');
        // this attribute is applicable only for downloadable
        $this->_model->beforeSave($product);
        $this->assertTrue($product->canAffectOptions());
        $this->assertFalse($product->hasData('links_purchased_separately'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetSku()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture
        $this->assertEquals('simple', $this->_model->getSku($product));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetOptionSku()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->assertEmpty($this->_model->getOptionSku($product));

        $product->load(1);
        // fixture
        $this->assertEquals('simple', $this->_model->getOptionSku($product));

        foreach ($product->getOptions() as $id => $option) {
            if ('field' == $option->getType()) {
                $product->addCustomOption('option_ids', $id);
                $quoteOption = clone $option;
                $product->addCustomOption("option_{$id}", $quoteOption);

                $this->assertEquals('simple-1-text', $this->_model->getOptionSku($product));
                break;
            }
        }
    }

    public function testGetWeight()
    {
        $product = new \Magento\Framework\Object();
        $this->assertEmpty($this->_model->getWeight($product));
        $product->setWeight('value');
        $this->assertEquals('value', $this->_model->getWeight($product));
    }

    public function testHasOptions()
    {
        $this->markTestIncomplete('Bug MAGE-2814');

        $product = new \Magento\Framework\Object();
        $this->assertFalse($this->_model->hasOptions($product));

        $product = new \Magento\Framework\Object(array('has_options' => true));
        $this->assertTrue($this->_model->hasOptions($product));

        $product = new \Magento\Framework\Object(array('is_recurring' => 1));
        $this->assertTrue($this->_model->hasOptions($product));
    }

    public function testHasRequiredOptions()
    {
        $product = new \Magento\Framework\Object();
        $this->assertFalse($this->_model->hasRequiredOptions($product));
        $product->setRequiredOptions(1);
        $this->assertTrue($this->_model->hasRequiredOptions($product));
    }

    public function testGetSetStoreFilter()
    {
        $product = new \Magento\Framework\Object();
        $this->assertNull($this->_model->getStoreFilter($product));
        $store = new \StdClass();
        $this->_model->setStoreFilter($store, $product);
        $this->assertSame($store, $this->_model->getStoreFilter($product));
    }

    public function testGetForceChildItemQtyChanges()
    {
        $this->assertFalse(
            $this->_model->getForceChildItemQtyChanges(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product')
            )
        );
    }

    public function testPrepareQuoteItemQty()
    {
        $this->assertEquals(
            3.0,
            $this->_model->prepareQuoteItemQty(
                3,
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product')
            )
        );
    }

    public function testAssignProductToOption()
    {
        $product = new \Magento\Framework\Object();
        $option = new \Magento\Framework\Object();
        $this->_model->assignProductToOption($product, $option, $product);
        $this->assertSame($product, $option->getProduct());

        $option = new \Magento\Framework\Object();
        $this->_model->assignProductToOption(null, $option, $product);
        $this->assertSame($product, $option->getProduct());
    }

    /**
     * @covers \Magento\Catalog\Model\Product\Type\AbstractType::isComposite
     * @covers \Magento\Catalog\Model\Product\Type\AbstractType::canUseQtyDecimals
     * @covers \Magento\Catalog\Model\Product\Type\AbstractType::setConfig
     */
    public function testSetConfig()
    {
        $this->assertFalse(
            $this->_model->isComposite(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product')
            )
        );
        $this->assertTrue($this->_model->canUseQtyDecimals());
        $config = array('composite' => 1, 'can_use_qty_decimals' => 0);
        $this->_model->setConfig($config);
        $this->assertTrue(
            $this->_model->isComposite(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product')
            )
        );
        $this->assertFalse($this->_model->canUseQtyDecimals());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetSearchableData()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load(1);
        // fixture
        $data = $this->_model->getSearchableData($product);
        $this->assertContains('Test Field', $data);
        $this->assertContains('Test Date and Time', $data);
        $this->assertContains('Test Select', $data);
        $this->assertContains('Test Radio', $data);
        $this->assertContains('Option 1', $data);
        $this->assertContains('Option 2', $data);
    }

    public function testGetProductsToPurchaseByReqGroups()
    {
        $product = new \StdClass();
        $this->assertSame(array(array($product)), $this->_model->getProductsToPurchaseByReqGroups($product));
        $this->_model->setConfig(array('composite' => 1));
        $this->assertEquals(array(), $this->_model->getProductsToPurchaseByReqGroups($product));
    }

    public function testProcessBuyRequest()
    {
        $this->assertEquals(array(), $this->_model->processBuyRequest(1, 2));
    }

    public function testCheckProductConfiguration()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $buyRequest = new \Magento\Framework\Object(array('qty' => 5));
        $this->_model->checkProductConfiguration($product, $buyRequest);
    }
}
