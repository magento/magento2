<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for customer export model
 */
namespace Magento\CustomerImportExport\Model\Export;

class CustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CustomerImportExport\Model\Export\Customer
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CustomerImportExport\Model\Export\Customer::class
        );
    }

    /**
     * Test export method
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customers.php
     */
    public function testExport()
    {
        $expectedAttributes = [];
        /** @var $collection \Magento\Customer\Model\ResourceModel\Attribute\Collection */
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Attribute\Collection::class
        );
        /** @var $attribute \Magento\Customer\Model\Attribute */
        foreach ($collection as $attribute) {
            $expectedAttributes[] = $attribute->getAttributeCode();
        }
        $expectedAttributes = array_diff($expectedAttributes, $this->_model->getDisabledAttributes());

        $this->_model->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\ImportExport\Model\Export\Adapter\Csv::class
            )
        );
        $data = $this->_model->export();
        $this->assertNotEmpty($data);

        $lines = $this->_csvToArray($data, 'email');

        $this->assertEquals(
            count($expectedAttributes),
            count(array_intersect($expectedAttributes, $lines['header'])),
            'Expected attribute codes were not exported'
        );

        $this->assertNotEmpty($lines['data'], 'No data was exported');

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $customers \Magento\Customer\Model\Customer[] */
        $customers = $objectManager->get(
            \Magento\Framework\Registry::class
        )->registry(
            '_fixture/Magento_ImportExport_Customer_Collection'
        );
        foreach ($customers as $key => $customer) {
            foreach ($expectedAttributes as $code) {
                if (!in_array($code, $this->_model->getDisabledAttributes()) && isset($lines[$key][$code])) {
                    $this->assertEquals(
                        $customer->getData($code),
                        $lines[$key][$code],
                        'Attribute "' . $code . '" is not equal'
                    );
                }
            }
        }
    }

    /**
     * Test entity type code value
     */
    public function testGetEntityTypeCode()
    {
        $this->assertEquals('customer', $this->_model->getEntityTypeCode());
    }

    /**
     * Test type of attribute collection
     */
    public function testGetAttributeCollection()
    {
        $this->assertInstanceOf(
            \Magento\Customer\Model\ResourceModel\Attribute\Collection::class,
            $this->_model->getAttributeCollection()
        );
    }

    /**
     * Test for method filterAttributeCollection()
     */
    public function testFilterAttributeCollection()
    {
        /** @var $collection \Magento\Customer\Model\ResourceModel\Attribute\Collection */
        $collection = $this->_model->getAttributeCollection();
        $collection = $this->_model->filterAttributeCollection($collection);
        /**
         * Check that disabled attributes is not existed in attribute collection
         */
        $existedAttributes = [];
        /** @var $attribute \Magento\Customer\Model\Attribute */
        foreach ($collection as $attribute) {
            $existedAttributes[] = $attribute->getAttributeCode();
        }
        $disabledAttributes = $this->_model->getDisabledAttributes();
        foreach ($disabledAttributes as $attributeCode) {
            $this->assertNotContains(
                $attributeCode,
                $existedAttributes,
                'Disabled attribute "' . $attributeCode . '" existed in collection'
            );
        }
        /**
         * Check that all overridden attributes were affected during filtering process
         */
        $overriddenAttributes = $this->_model->getOverriddenAttributes();
        /** @var $attribute \Magento\Customer\Model\Attribute */
        foreach ($collection as $attribute) {
            if (isset($overriddenAttributes[$attribute->getAttributeCode()])) {
                foreach ($overriddenAttributes[$attribute->getAttributeCode()] as $propertyKey => $property) {
                    $this->assertEquals(
                        $property,
                        $attribute->getData($propertyKey),
                        'Value of property "' . $propertyKey . '" is not equals'
                    );
                }
            }
        }
    }

    /**
     * Test for method filterEntityCollection()
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customers.php
     */
    public function testFilterEntityCollection()
    {
        $createdAtDate = '2038-01-01';

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /**
         * Change created_at date of first customer for future filter test.
         */
        $customers = $objectManager->get(
            \Magento\Framework\Registry::class
        )->registry(
            '_fixture/Magento_ImportExport_Customer_Collection'
        );
        $customers[0]->setCreatedAt($createdAtDate);
        $customers[0]->save();
        /**
         * Change type of created_at attribute. In this case we have possibility to test date rage filter
         */
        $attributeCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Attribute\Collection::class
        );
        $attributeCollection->addFieldToFilter('attribute_code', 'created_at');
        /** @var $createdAtAttribute \Magento\Customer\Model\Attribute */
        $createdAtAttribute = $attributeCollection->getFirstItem();
        $createdAtAttribute->setBackendType('datetime');
        $createdAtAttribute->save();
        /**
         * Prepare filter.asd
         */
        $parameters = [
            \Magento\ImportExport\Model\Export::FILTER_ELEMENT_GROUP => [
                'email' => 'example.com',
                'created_at' => [$createdAtDate, ''],
                'store_id' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    \Magento\Store\Model\StoreManagerInterface::class
                )->getStore()->getId()
            ]
        ];
        $this->_model->setParameters($parameters);
        /** @var $customers \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $collection = $this->_model->filterEntityCollection(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Customer\Model\ResourceModel\Customer\Collection::class
            )
        );
        $collection->load();

        $this->assertCount(1, $collection);
        $this->assertEquals($customers[0]->getId(), $collection->getFirstItem()->getId());
    }

    /**
     * Export CSV string to array
     *
     * @param string $content
     * @param mixed $entityId
     * @return array
     */
    protected function _csvToArray($content, $entityId = null)
    {
        $data = ['header' => [], 'data' => []];

        $lines = str_getcsv($content, "\n");
        foreach ($lines as $index => $line) {
            if ($index == 0) {
                $data['header'] = str_getcsv($line);
            } else {
                $row = array_combine($data['header'], str_getcsv($line));
                if ($entityId !== null && !empty($row[$entityId])) {
                    $data['data'][$row[$entityId]] = $row;
                } else {
                    $data['data'][] = $row;
                }
            }
        }
        return $data;
    }
}
