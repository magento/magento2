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

/**
 * Test for eav abstract export model
 */
namespace Magento\ImportExport\Model\Export\Entity;

class AbstractEavTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Skipped attribute codes
     *
     * @var array
     */
    protected static $_skippedAttributes = array('confirmation', 'lastname');

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

    protected function setUp()
    {
        /** @var \Magento\TestFramework\ObjectManager  $objectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $customerAttributes = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Resource\Attribute\Collection'
        );

        $this->_model = $this->getMockForAbstractClass(
            'Magento\ImportExport\Model\Export\Entity\AbstractEav',
            array(),
            '',
            false
        );
        $this->_model->expects(
            $this->any()
        )->method(
            'getEntityTypeCode'
        )->will(
            $this->returnValue($this->_entityCode)
        );
        $this->_model->expects(
            $this->any()
        )->method(
            'getAttributeCollection'
        )->will(
            $this->returnValue($customerAttributes)
        );
        $this->_model->__construct(
            $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface'),
            $objectManager->get('Magento\Store\Model\StoreManager'),
            $objectManager->get('Magento\ImportExport\Model\Export\Factory'),
            $objectManager->get('Magento\ImportExport\Model\Resource\CollectionByPagesIteratorFactory'),
            $objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface'),
            $objectManager->get('Magento\Eav\Model\Config')
        );
    }

    /**
     * Test for method getEntityTypeId()
     */
    public function testGetEntityTypeId()
    {
        $entityCode = 'customer';
        $entityId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Eav\Model\Config'
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
        /** @var $attributeCollection \Magento\Customer\Model\Resource\Attribute\Collection */
        $attributeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Resource\Attribute\Collection'
        );
        $attributeCollection->addFieldToFilter('attribute_code', 'gender');
        /** @var $attribute \Magento\Customer\Model\Attribute */
        $attribute = $attributeCollection->getFirstItem();

        $expectedOptions = array();
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
        /** @var $attributeCollection \Magento\Customer\Model\Resource\Attribute\Collection */
        $attributeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Resource\Attribute\Collection'
        );
        $attributeCollection->addFieldToFilter('attribute_code', array('in' => self::$_skippedAttributes));
        $skippedAttributes = array();
        /** @var $attribute  \Magento\Customer\Model\Attribute */
        foreach ($attributeCollection as $attribute) {
            $skippedAttributes[$attribute->getAttributeCode()] = $attribute->getId();
        }

        return array(\Magento\ImportExport\Model\Export::FILTER_ELEMENT_SKIP => $skippedAttributes);
    }
}
