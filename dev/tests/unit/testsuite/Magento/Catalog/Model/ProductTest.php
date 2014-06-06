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
namespace Magento\Catalog\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $model;

    /**
     * @var \Magento\Indexer\Model\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryIndexerMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFlatProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productPriceProcessor;

    /**
     * @var Product\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeInstanceMock;

    /**
     * @var Product\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionInstanceMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_priceInfoMock;

    public function setUp()
    {
        $this->categoryIndexerMock = $this->getMockForAbstractClass('\Magento\Indexer\Model\IndexerInterface');

        $this->productFlatProcessor = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Flat\Processor',
            array(),
            array(),
            '',
            false
        );

        $this->_priceInfoMock = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $this->productTypeInstanceMock = $this->getMock('Magento\Catalog\Model\Product\Type', [], [], '', false);
        $this->productPriceProcessor = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Price\Processor',
            array(),
            array(),
            '',
            false
        );

        $stateMock = $this->getMock('Magento\FrameworkApp\State', array('getAreaCode'), array(), '', false);
        $stateMock->expects($this->any())
            ->method('getAreaCode')
            ->will($this->returnValue(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE));

        $eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $actionValidatorMock = $this->getMock(
            '\Magento\Framework\Model\ActionValidator\RemoveAction',
            [],
            [],
            '',
            false
        );
        $actionValidatorMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $cacheInterfaceMock = $this->getMock('Magento\Framework\App\CacheInterface');

        $contextMock = $this->getMock(
            '\Magento\Framework\Model\Context',
            array('getEventDispatcher', 'getCacheManager', 'getAppState', 'getActionValidator'), array(), '', false
        );
        $contextMock->expects($this->any())->method('getAppState')->will($this->returnValue($stateMock));
        $contextMock->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($eventManagerMock));
        $contextMock->expects($this->any())
            ->method('getCacheManager')
            ->will($this->returnValue($cacheInterfaceMock));
        $contextMock->expects($this->any())
            ->method('getActionValidator')
            ->will($this->returnValue($actionValidatorMock));

        $this->optionInstanceMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Option')
            ->setMethods(['setProduct', 'saveOptions', '__wakeup', '__sleep'])
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Model\Product',
            [
                'context' => $contextMock,
                'catalogProductType' => $this->productTypeInstanceMock,
                'categoryIndexer' => $this->categoryIndexerMock,
                'productFlatIndexerProcessor' => $this->productFlatProcessor,
                'productPriceIndexerProcessor' => $this->productPriceProcessor,
                'catalogProductOption' => $this->optionInstanceMock,
                'data' => array('id' => 1)
            ]
        );
    }

    public function testIndexerAfterDeleteCommitProduct()
    {
        $this->categoryIndexerMock->expects($this->once())->method('reindexRow');
        $this->productFlatProcessor->expects($this->once())->method('reindexRow');
        $this->productPriceProcessor->expects($this->once())->method('reindexRow');
        $this->assertSame($this->model, $this->model->delete());
    }

    public function testReindex()
    {
        $this->categoryIndexerMock->expects($this->once())->method('reindexRow');
        $this->productFlatProcessor->expects($this->once())->method('reindexRow');
        $this->assertNull($this->model->reindex());
    }

    public function testPriceReindexCallback()
    {
        $this->productPriceProcessor->expects($this->once())->method('reindexRow');
        $this->assertNull($this->model->priceReindexCallback());
    }

    /**
     * @dataProvider getIdentitiesProvider
     * @param array $expected
     * @param array $origData
     * @param array $data
     * @param bool $isDeleted
     */
    public function testGetIdentities($expected, $origData, $data, $isDeleted = false)
    {
        $this->model->setIdFieldName('id');
        if (is_array($origData)) {
            foreach ($origData as $key => $value) {
                $this->model->setOrigData($key, $value);
            }
        }
        $this->model->setData($data);
        $this->model->isDeleted($isDeleted);
        $this->assertEquals($expected, $this->model->getIdentities());
    }

    /**
     * @return array
     */
    public function getIdentitiesProvider()
    {
        return array(
            array(
                array('catalog_product_1'),
                array('id' => 1, 'name' => 'value', 'category_ids' => array(1)),
                array('id' => 1, 'name' => 'value', 'category_ids' => array(1))
            ),
            array(
                array('catalog_product_1', 'catalog_category_product_1'),
                null,
                array(
                    'id' => 1,
                    'name' => 'value',
                    'category_ids' => array(1),
                    'affected_category_ids' => array(1),
                    'is_changed_categories' => true
                )
            )
        );
    }

    /**
     * Test retrieving price Info
     */
    public function testGetPriceInfo()
    {
        $this->productTypeInstanceMock->expects($this->once())
            ->method('getPriceInfo')
            ->with($this->equalTo($this->model))
            ->will($this->returnValue($this->_priceInfoMock));
        $this->assertEquals($this->model->getPriceInfo(), $this->_priceInfoMock);
    }

    /**
     * Test for set qty
     */
    public function testSetQty()
    {
        $this->productTypeInstanceMock->expects($this->once())
            ->method('getPriceInfo')
            ->with($this->equalTo($this->model))
            ->will($this->returnValue($this->_priceInfoMock));
        $this->assertEquals($this->model, $this->model->setQty(1));
        $this->assertEquals($this->model->getPriceInfo(), $this->_priceInfoMock);
    }

    /**
     * Test reload PriceInfo
     */
    public function testReloadPriceInfo()
    {
        $this->productTypeInstanceMock->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->with($this->equalTo($this->model))
            ->will($this->returnValue($this->_priceInfoMock));
        $this->assertEquals($this->_priceInfoMock, $this->model->getPriceInfo());
        $this->assertEquals($this->_priceInfoMock, $this->model->reloadPriceInfo());
    }

    /**
     * Test for get qty
     */
    public function testGetQty()
    {
        $this->model->setQty(1);
        $this->assertEquals(1, $this->model->getQty());
    }

    /**
     *  Test for `save` method
     */
    public function testSave()
    {
        $this->model->setIsDuplicate(false);
        $this->configureSaveTest();
        $this->optionInstanceMock->expects($this->any())->method('setProduct')->will($this->returnSelf());
        $this->optionInstanceMock->expects($this->once())->method('saveOptions')->will($this->returnSelf());
        $this->model->save();
    }

    /**
     *  Test for `save` method for duplicated product
     */
    public function testSaveAndDuplicate()
    {
        $this->model->setIsDuplicate(true);
        $this->configureSaveTest();
        $this->model->save();
    }

    /**
     * Configure environment for `testSave` and `testSaveAndDuplicate` methods
     * @return array
     */
    protected function configureSaveTest()
    {
        $productTypeMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Type\Simple')
            ->disableOriginalConstructor()->setMethods(['beforeSave', 'save'])->getMock();
        $productTypeMock->expects($this->once())->method('beforeSave')->will($this->returnSelf());
        $productTypeMock->expects($this->once())->method('save')->will($this->returnSelf());

        $this->productTypeInstanceMock->expects($this->once())->method('factory')->with($this->model)
            ->will($this->returnValue($productTypeMock));

        $this->model->getResource()->expects($this->any())->method('addCommitCallback')->will($this->returnSelf());
        $this->model->getResource()->expects($this->any())->method('commit')->will($this->returnSelf());
    }
}
