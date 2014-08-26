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
namespace Magento\Customer\Service\V1\Data\Eav;

class AttributeMetadataDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $configEav;

    /**
     * @var \Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory
     */
    protected $attrFormCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Resource\Form\Attribute\Collection
     */
    protected $attrFormCollection;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var AttributeMetadataDataProvider
     */
    protected $attributeMetadataProvider;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->configEav = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute', 'getEntityAttributeCodes'])
            ->getMock();

        $this->attrFormCollection = $this->getMockBuilder('Magento\Customer\Model\Resource\Form\Attribute\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['setStore', 'setEntityType', 'addFormCodeFilter', 'setSortOrder'])
            ->getMock();

        $this->attrFormCollectionFactory = $this->getMockBuilder(
            'Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $this->attrFormCollectionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->attrFormCollection));

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManager')
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMock();

        $this->attributeMetadataProvider = $this->objectManager->getObject(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadataDataProvider',
            [
                'eavConfig' => $this->configEav,
                'attrFormCollectionFactory' => $this->attrFormCollectionFactory,
                'storeManager' => $this->storeManager
            ]
        );
    }

    public function testGetAttribute()
    {
        $entityType = 'entityType';
        $attributeCode = 'attributeCode';
        $value = 'value';

        $this->configEav->expects($this->once())
            ->method('getAttribute')
            ->with($entityType, $attributeCode)
            ->will($this->returnValue($value));

        $actualValue = $this->attributeMetadataProvider->getAttribute($entityType, $attributeCode);

        $this->assertEquals($value, $actualValue);
    }

    public function testGetAllAttributeCodesWithStoreId()
    {
        $entityType = 'entityType';
        $attributeSetId = 'attributeSetId';
        $storeId = 'storeId';
        $value = 'value';

        $objectCodes = new \Magento\Framework\Object(
            [
                'store_id' => $storeId,
                'attribute_set_id' => $attributeSetId,
            ]
        );

        $this->configEav->expects($this->once())
            ->method('getEntityAttributeCodes')
            ->with($entityType, $objectCodes)
            ->will($this->returnValue($value));

        $actualValue = $this->attributeMetadataProvider->getAllAttributeCodes($entityType, $attributeSetId, $storeId);

        $this->assertEquals($value, $actualValue);
    }

    public function testGetAllAttributeCodesWithoutStoreId()
    {
        $entityType = 'entityType';
        $attributeSetId = 'attributeSetId';
        $storeId = 'storeId';
        $value = 'value';

        $store = $this->getMockBuilder('Magento\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($storeId));

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($store));

        $objectCodes = new \Magento\Framework\Object(
            [
                'store_id' => $storeId,
                'attribute_set_id' => $attributeSetId,
            ]
        );

        $this->configEav->expects($this->once())
            ->method('getEntityAttributeCodes')
            ->with($entityType, $objectCodes)
            ->will($this->returnValue($value));

        $actualValue = $this->attributeMetadataProvider->getAllAttributeCodes($entityType, $attributeSetId, null);

        $this->assertEquals($value, $actualValue);
    }

    public function testLoadAttributesCollection()
    {
        $entityType = 'entityType';
        $formCode = 'formCode';

        $store = $this->getMockBuilder('Magento\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($store));

        $this->attrFormCollection->expects($this->once())
            ->method('setStore')
            ->with($store)
            ->will($this->returnSelf());
        $this->attrFormCollection->expects($this->once())
            ->method('setEntityType')
            ->with($entityType)
            ->will($this->returnSelf());
        $this->attrFormCollection->expects($this->once())
            ->method('addFormCodeFilter')
            ->with($formCode)
            ->will($this->returnSelf());
        $this->attrFormCollection->expects($this->once())
            ->method('setSortOrder')
            ->will($this->returnSelf());

        $actualValue = $this->attributeMetadataProvider->loadAttributesCollection($entityType, $formCode);

        $this->assertEquals($this->attrFormCollection, $actualValue);
    }
}
