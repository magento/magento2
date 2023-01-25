<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for eav abstract export model
 */
namespace Magento\ImportExport\Model\Export\Entity;

class AbstractEavTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Skipped attribute codes
     *
     * @var array
     */
    protected static $_skippedAttributes = ['confirmation', 'lastname'];

    /**
     * @var \Magento\ImportExport\Model\Export\Entity\AbstractEav
     */
    protected $_model;

    /**
     * Entity code
     *
     * @var string
     */
    protected $_entityCode = 'customer';

    protected function setUp(): void
    {
        /** @var \Magento\TestFramework\ObjectManager  $objectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $customerAttributes = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Attribute\Collection::class
        );

        $this->_model = $this->getMockBuilder(\Magento\ImportExport\Model\Export\Entity\AbstractEav::class)
            ->setMethods(['getEntityTypeCode', 'getAttributeCollection'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->_model->expects(
            $this->any()
        )->method(
            'getEntityTypeCode'
        )->willReturn(
            $this->_entityCode
        );
        $this->_model->expects(
            $this->any()
        )->method(
            'getAttributeCollection'
        )->willReturn(
            $customerAttributes
        );
        $this->_model->__construct(
            $objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class),
            $objectManager->get(\Magento\Store\Model\StoreManager::class),
            $objectManager->get(\Magento\ImportExport\Model\Export\Factory::class),
            $objectManager->get(\Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory::class),
            $objectManager->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class),
            $objectManager->get(\Magento\Eav\Model\Config::class)
        );
    }

    /**
     * Test for method getEntityTypeId()
     */
    public function testGetEntityTypeId()
    {
        $entityCode = 'customer';
        $entityId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Eav\Model\Config::class
        )->getEntityType(
            $entityCode
        )->getEntityTypeId();

        $this->assertEquals($entityId, $this->_model->getEntityTypeId());
    }

    /**
     * Test for method _getExportAttrCodes()
     *
     * @covers \Magento\ImportExport\Model\Export\Entity\AbstractEav::_getExportAttributeCodes
     */
    public function testGetExportAttrCodes()
    {
        $this->_model->setParameters($this->_getSkippedAttributes());
        $method = new \ReflectionMethod($this->_model, '_getExportAttributeCodes');
        $method->setAccessible(true);
        $attributes = $method->invoke($this->_model);
        foreach (self::$_skippedAttributes as $code) {
            $this->assertNotContains($code, $attributes);
        }
    }

    /**
     * Test for method getAttributeOptions()
     */
    public function testGetAttributeOptions()
    {
        /** @var $attributeCollection \Magento\Customer\Model\ResourceModel\Attribute\Collection */
        $attributeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Attribute\Collection::class
        );
        $attributeCollection->addFieldToFilter('attribute_code', 'gender');
        /** @var $attribute \Magento\Customer\Model\Attribute */
        $attribute = $attributeCollection->getFirstItem();

        $expectedOptions = [];
        foreach ($attribute->getSource()->getAllOptions(false) as $option) {
            $expectedOptions[$option['value']] = $option['label'];
        }

        $actualOptions = $this->_model->getAttributeOptions($attribute);
        $this->assertEquals($expectedOptions, $actualOptions);
    }

    /**
     * Retrieve list of skipped attributes
     *
     * @return array
     */
    protected function _getSkippedAttributes()
    {
        /** @var $attributeCollection \Magento\Customer\Model\ResourceModel\Attribute\Collection */
        $attributeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Attribute\Collection::class
        );
        $attributeCollection->addFieldToFilter('attribute_code', ['in' => self::$_skippedAttributes]);
        $skippedAttributes = [];
        /** @var $attribute  \Magento\Customer\Model\Attribute */
        foreach ($attributeCollection as $attribute) {
            $skippedAttributes[$attribute->getAttributeCode()] = $attribute->getId();
        }

        return [\Magento\ImportExport\Model\Export::FILTER_ELEMENT_SKIP => $skippedAttributes];
    }
}
