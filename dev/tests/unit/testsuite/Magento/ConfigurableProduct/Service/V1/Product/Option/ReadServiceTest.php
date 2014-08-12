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

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute as ResourceAttribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Collection;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class ReadServiceTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    const TYPE_FIELD_NAME = 'frontend_input';
    const ATTRIBUTE_ID_FIELD_NAME = 'product_super_attribute_id';
    const OPTION_TYPE = 'select';
    /**
     * @var \Magento\Catalog\Model\Resource\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavAttribute;
    /**
     * @var ResourceAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeResource;
    /**
     * @var \Magento\ConfigurableProduct\Service\V1\Product\Option\ReadService
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\ProductRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\ConfigurableProduct\Service\V1\Data\OptionConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionConverter;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productType;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $option;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configurableAttributeCollection;

    /**
     * @var \Magento\ConfigurableProduct\Service\V1\Data\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadata;
    /**
     * @var \Magento\Catalog\Model\System\Config\Source\Inputtype|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inputType;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->productRepository = $this->getMockBuilder('Magento\Catalog\Model\ProductRepository')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->productType = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable'
        )
            ->setMethods(['getConfigurableAttributeCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getTypeId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->option = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute')
            ->setMethods(['__wakeup', 'getId', 'getProductAttribute'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavAttribute = $this->getMockBuilder('Magento\Catalog\Model\Resource\Eav\Attribute')
            ->setMethods(['getData', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeResource = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute'
        )
            ->setMethods(['getIdFieldName', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurableAttributeCollection = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Collection'
        )
            ->setMethods(['getResource', 'addFieldToFilter', 'getFirstItem'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionConverter = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Service\V1\Data\OptionConverter'
        )
            ->setMethods(['convertFromModel'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadata = $this->getMockBuilder('\Magento\ConfigurableProduct\Service\V1\Data\Option')
            ->disableOriginalConstructor()
            ->getMock();

        $this->inputType = $this->getMockBuilder('Magento\Catalog\Model\System\Config\Source\Inputtype')
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            'Magento\ConfigurableProduct\Service\V1\Product\Option\ReadService',
            [
                'productRepository' => $this->productRepository,
                'optionConverter'   => $this->optionConverter,
                'configurableType'  => $this->productType,
                'inputType'         => $this->inputType,
            ]
        );
    }

    public function testGetList()
    {
        $productSku = 'oneSku';

        $this->productRepository->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(ConfigurableType::TYPE_CODE));

        $this->productType->expects($this->once())->method('getConfigurableAttributeCollection')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue([$this->option]));

        $this->optionConverter->expects($this->once())->method('convertFromModel')
            ->with($this->equalTo($this->option))
            ->will($this->returnValue($this->metadata));

        $this->assertEquals([$this->metadata], $this->model->getList($productSku));
    }

    public function testGet()
    {
        $productSku = 'oneSku';
        $optionId = 3;

        $this->productRepository->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(ConfigurableType::TYPE_CODE));

        $this->productType->expects($this->once())->method('getConfigurableAttributeCollection')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue($this->configurableAttributeCollection));

        $this->configurableAttributeCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with(self::ATTRIBUTE_ID_FIELD_NAME, $optionId);

        $this->configurableAttributeCollection->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($this->option));

        $this->option->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($optionId));

        $this->attributeResource->expects($this->once())
            ->method('getIdFieldName')
            ->will($this->returnValue(self::ATTRIBUTE_ID_FIELD_NAME));

        $this->configurableAttributeCollection->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($this->attributeResource));

        $this->optionConverter->expects($this->once())->method('convertFromModel')
            ->with($this->equalTo($this->option))
            ->will($this->returnValue($this->metadata));

        $this->assertEquals($this->metadata, $this->model->get($productSku, $optionId));
    }

    public function testGetTypes()
    {
        $optionArray = array(
            array('value' => 'multiselect', 'label' => __('Multiple Select')),
            array('value' => 'select', 'label' => __('Dropdown'))
        );
        $expectedResult = ['multiselect', 'select'];

        $this->inputType->expects($this->once())
            ->method('toOptionArray')
            ->will($this->returnValue($optionArray));
        $this->assertEquals($expectedResult, $this->model->getTypes());
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested option doesn't exist: 3
     */
    public function testGetNoSuchEntityException()
    {
        $productSku = 'oneSku';
        $optionId = 3;

        $this->productRepository->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(ConfigurableType::TYPE_CODE));

        $this->productType->expects($this->once())->method('getConfigurableAttributeCollection')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue($this->configurableAttributeCollection));

        $this->configurableAttributeCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with(self::ATTRIBUTE_ID_FIELD_NAME, $optionId)
            ->will($this->returnSelf());

        $this->configurableAttributeCollection->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($this->option));

        $this->option->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));

        $this->attributeResource->expects($this->once())
            ->method('getIdFieldName')
            ->will($this->returnValue(self::ATTRIBUTE_ID_FIELD_NAME));

        $this->configurableAttributeCollection->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($this->attributeResource));

        $this->model->get($productSku, $optionId);
    }

    /**
     * @expectedException \Magento\Webapi\Exception
     * @expectedExceptionCode 403
     * @expectedExceptionMessage Only implemented for configurable product: oneSku
     */
    public function testGetListWebApiException()
    {
        $productSku = 'oneSku';

        $this->productRepository->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE));

        $this->model->getList($productSku);
    }
}
