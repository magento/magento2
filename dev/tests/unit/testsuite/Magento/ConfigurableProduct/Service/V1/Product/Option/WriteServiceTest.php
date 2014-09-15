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
namespace Magento\ConfigurableProduct\Service\V1\Product\Option;

use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory as ConfigurableAttributeFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Collection;
use Magento\ConfigurableProduct\Service\V1\Data\Option;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class WriteServiceTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManager */
    protected $objectManager;

    /**
     * @var ProductRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var ConfigurableAttributeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $confAttributeFactoryMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var \Magento\ConfigurableProduct\Service\V1\Data\OptionBuilder
     */
    protected $optionBuilder;

    /**
     * @var \Magento\ConfigurableProduct\Service\V1\Product\Option\WriteService
     */
    protected $writeService;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productTypeMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeCollectionMock;

    /**
     * @var Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMock;

    /**
     * @var \Magento\ConfigurableProduct\Service\V1\Data\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionMock;

    /**
     * @var \Magento\ConfigurableProduct\Service\V1\Data\OptionConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionConverterMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->productRepositoryMock = $this->getMockBuilder('Magento\Catalog\Model\ProductRepository')
            ->disableOriginalConstructor()->setMethods(['get'])->getMock();

        $this->confAttributeFactoryMock = $this
            ->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory')
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $this->eavConfigMock = $this
            ->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getSku', 'getTypeId', '__wakeup', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->productTypeMock = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
            ->setMethods(['getConfigurableAttributeCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionConverterMock = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Service\V1\Data\OptionConverter'
        )
            ->setMethods(['getModelFromData', 'convertFromModel', 'convertArrayFromData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionMock = $this->getMockBuilder('Magento\ConfigurableProduct\Service\V1\Data\Option')
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeCollectionMock = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Collection'
        )
            ->setMethods(['getItemById'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeMock = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute'
        )
            ->setMethods(['delete', '__wakeup', 'load', 'save', 'getId', 'getProductId'])
            ->disableOriginalConstructor()
            ->getMock();

        $storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface', [], [], '', false);
        $storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue(new \Magento\Framework\Object()));

        $this->writeService = $this->objectManager->getObject(
            'Magento\ConfigurableProduct\Service\V1\Product\Option\WriteService',
            [
                'productRepository' => $this->productRepositoryMock,
                'configurableAttributeFactory' => $this->confAttributeFactoryMock,
                'eavConfig' => $this->eavConfigMock,
                'storeManager' => $storeManagerMock,
                'productType' => $this->productTypeMock,
                'optionConverter' => $this->optionConverterMock
            ]
        );

        $this->optionBuilder = $this->objectManager
            ->getObject('Magento\ConfigurableProduct\Service\V1\Data\OptionBuilder');
    }

    /**
     * Add configurable option test
     */
    public function testAdd()
    {
        $productSku = 'test_sku';
        $option = $this->getOption();

        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['save', 'setConfigurableAttributesData', 'setStoreId', 'getTypeId', 'setTypeId', '__sleep', '__wakeup'],
            [],
            '',
            false
        );
        $productMock->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(ProductType::TYPE_SIMPLE));
        $this->productRepositoryMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($productMock));

        $confAttributeMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute',
            [],
            [],
            '',
            false
        );
        $this->confAttributeFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($confAttributeMock));

        $confAttributeMock->expects($this->exactly(2))->method('loadByProductAndAttribute');
        $confAttributeMock->expects($this->at(1))->method('getId')->will($this->returnValue(null));
        $confAttributeMock->expects($this->at(3))->method('getId')->will($this->returnValue(1));

        $productMock->expects($this->once())->method('setTypeId')->with(ConfigurableType::TYPE_CODE);
        $productMock->expects($this->once())->method('setConfigurableAttributesData');
        $productMock->expects($this->once())->method('setStoreId')->with(0);
        $productMock->expects($this->once())->method('save');

        $this->optionConverterMock->expects($this->once())->method('convertArrayFromData')->with($option);

        $this->writeService->add($productSku, $option);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAddLoadNewOptionCouldNotSaveException()
    {
        $productSku = 'test_sku';
        $option = $this->getOption();

        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['save', 'setConfigurableAttributesData', 'setStoreId', 'getTypeId', 'setTypeId', '__sleep', '__wakeup'],
            [],
            '',
            false
        );
        $productMock->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(ProductType::TYPE_SIMPLE));
        $this->productRepositoryMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($productMock));

        $confAttributeMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute',
            [],
            [],
            '',
            false
        );
        $this->confAttributeFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($confAttributeMock));

        $confAttributeMock->expects($this->exactly(2))->method('loadByProductAndAttribute');
        $confAttributeMock->expects($this->at(1))->method('getId')->will($this->returnValue(null));
        $confAttributeMock->expects($this->at(2))->method('getId')->will($this->returnValue(1));
        $confAttributeMock->expects($this->at(3))->method('getId')->will($this->returnValue(null));

        $productMock->expects($this->once())->method('setTypeId')->with(ConfigurableType::TYPE_CODE);
        $productMock->expects($this->once())->method('setConfigurableAttributesData');
        $productMock->expects($this->once())->method('setStoreId')->with(0);
        $productMock->expects($this->once())->method('save');

        $this->optionConverterMock->expects($this->once())->method('convertArrayFromData')->with($option);

        $this->writeService->add($productSku, $option);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAddHasOptionCouldNotSaveException()
    {
        $productSku = 'test_sku';
        $option = $this->getOption();

        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['save', 'setConfigurableAttributesData', 'setStoreId', 'getTypeId', 'setTypeId', '__sleep', '__wakeup'],
            [],
            '',
            false
        );
        $productMock->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(ProductType::TYPE_SIMPLE));
        $this->productRepositoryMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($productMock));

        $confAttributeMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute',
            [],
            [],
            '',
            false
        );
        $this->confAttributeFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($confAttributeMock));

        $confAttributeMock->expects($this->once())->method('loadByProductAndAttribute');
        $confAttributeMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $this->writeService->add($productSku, $option);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAddSAveExceptionCouldNotSaveException()
    {
        $productSku = 'test_sku';
        $option = $this->getOption();

        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['save', 'setConfigurableAttributesData', 'setStoreId', 'getTypeId', 'setTypeId', '__sleep', '__wakeup'],
            [],
            '',
            false
        );
        $productMock->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(ProductType::TYPE_SIMPLE));
        $this->productRepositoryMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($productMock));

        $confAttributeMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute',
            [],
            [],
            '',
            false
        );
        $this->confAttributeFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($confAttributeMock));

        $confAttributeMock->expects($this->once())->method('loadByProductAndAttribute');
        $confAttributeMock->expects($this->once())->method('getId')->will($this->returnValue(null));

        $productMock->expects($this->once())->method('setTypeId')->with(ConfigurableType::TYPE_CODE);
        $productMock->expects($this->once())->method('setConfigurableAttributesData');
        $productMock->expects($this->once())->method('setStoreId')->with(0);
        $productMock->expects($this->once())->method('save')
            ->will(
                $this->returnCallback(
                    function () {
                        throw new \Exception();
                    }
                )
            );

        $this->writeService->add($productSku, $option);
    }

    /**
     * Invalid product type check
     *
     * @expectedException \InvalidArgumentException
     */
    public function testAddInvalidProductType()
    {
        $productSku = 'test_sku';
        $option = $this->getOption();

        $productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(ProductType::TYPE_BUNDLE));
        $this->productRepositoryMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($productMock));

        $this->writeService->add($productSku, $option);
    }

    public function testUpdate()
    {
        $productSku = 'productSku';
        $optionId = 3;

        $this->productRepositoryMock->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->productMock));

        $this->productMock->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(ConfigurableType::TYPE_CODE));
        $this->productMock->expects($this->any())->method('getId')
            ->will($this->returnValue($optionId));

        $this->confAttributeFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->attributeMock));

        $this->attributeMock->expects($this->once())->method('load')->with($this->equalTo($optionId));
        $this->attributeMock->expects($this->any())->method('getId')->will($this->returnValue($optionId));
        $this->attributeMock->expects($this->any())->method('getProductId')->will($this->returnValue($optionId));
        $this->attributeMock->expects($this->any())->method('save');

        $this->optionConverterMock->expects($this->once())->method('getModelFromData')
            ->with($this->equalTo($this->optionMock), $this->equalTo($this->attributeMock))
            ->will($this->returnValue($this->attributeMock));

        $this->writeService->update($productSku, $optionId, $this->optionMock);
    }

    /**
     * #@expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testUpdateCouldNotSaveException()
    {
        $productSku = 'productSku';
        $optionId = 3;

        $this->productRepositoryMock->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->productMock));

        $this->productMock->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(ConfigurableType::TYPE_CODE));
        $this->productMock->expects($this->any())->method('getId')
            ->will($this->returnValue($optionId));

        $this->confAttributeFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->attributeMock));

        $this->attributeMock->expects($this->once())->method('load')->with($this->equalTo($optionId));
        $this->attributeMock->expects($this->any())->method('getId')->will($this->returnValue($optionId));
        $this->attributeMock->expects($this->any())->method('getProductId')->will($this->returnValue($optionId));
        $this->attributeMock->expects($this->any())->method('save')
            ->will(
                $this->returnCallback(
                    function () {
                        throw new \Exception();
                    }
                )
            );

        $this->optionConverterMock->expects($this->once())->method('getModelFromData')
            ->with($this->equalTo($this->optionMock), $this->equalTo($this->attributeMock))
            ->will($this->returnValue($this->attributeMock));

        $this->writeService->update($productSku, $optionId, $this->optionMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testUpdateNoSuchEntityException()
    {
        $productSku = 'productSku';
        $optionId = 3;

        $this->productRepositoryMock->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->productMock));

        $this->productMock->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(ConfigurableType::TYPE_CODE));

        $this->confAttributeFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->attributeMock));

        $this->attributeMock->expects($this->once())->method('load')->with($this->equalTo($optionId));
        $this->attributeMock->expects($this->any())->method('getId')->will($this->returnValue(0));

        $this->writeService->update($productSku, $optionId, $this->optionMock);
    }

    public function testRemove()
    {
        $productSku = 'productSku';
        $optionId = 3;

        $this->productRepositoryMock->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->productMock));

        $this->productMock->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(ConfigurableType::TYPE_CODE));

        $this->productTypeMock->expects($this->once())->method('getConfigurableAttributeCollection')
            ->with($this->equalTo($this->productMock))
            ->will($this->returnValue($this->attributeCollectionMock));

        $this->attributeCollectionMock->expects($this->once())->method('getItemById')
            ->with($this->equalTo($optionId))
            ->will($this->returnValue($this->attributeMock));

        $this->attributeMock->expects($this->once())->method('delete');

        $this->assertTrue($this->writeService->remove($productSku, $optionId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRemoveNoSuchEntityException()
    {
        $productSku = 'productSku';
        $optionId = 3;

        $this->productRepositoryMock->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->productMock));

        $this->productMock->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(ConfigurableType::TYPE_CODE));

        $this->productTypeMock->expects($this->once())->method('getConfigurableAttributeCollection')
            ->with($this->equalTo($this->productMock))
            ->will($this->returnValue($this->attributeCollectionMock));

        $this->attributeCollectionMock->expects($this->once())->method('getItemById')
            ->with($this->equalTo($optionId))
            ->will($this->returnValue(null));

        $this->writeService->remove($productSku, $optionId);
    }

    /**
     * @expectedException \Magento\Webapi\Exception
     */
    public function testRemoveWebApiException()
    {
        $productSku = 'productSku';

        $this->productRepositoryMock->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->productMock));

        $this->productMock->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(ProductType::TYPE_SIMPLE));
        $this->productMock->expects($this->once())->method('getSku')
            ->will($this->returnValue($productSku));

        $this->writeService->remove($productSku, 3);
    }

    /**
     * Return instance of option for configurable product
     *
     * @return \Magento\Framework\Service\Data\AbstractExtensibleObject
     */
    private function getOption()
    {
        $data = [
            Option::ID => 1,
            Option::ATTRIBUTE_ID => 2,
            Option::LABEL => 'Test Label',
            Option::POSITION => 1,
            Option::USE_DEFAULT => true,
            Option::VALUES => [
                [
                    'index' => 1,
                    'price' => 12,
                    'percent' => true
                ]
            ]
        ];

        return $this->optionBuilder->populateWithArray($data)->create();
    }
}
