<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerImportExport\Model\Export;

use Magento\Framework\Registry;
use Magento\Customer\Model\Attribute;
use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\Import;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ImportExport\Model\Export\Adapter\Csv;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\CustomerImportExport\Model\Export\Customer;
use Magento\Customer\Model\ResourceModel\Attribute\Collection;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;

/**
 * Tests for customer export model.
 *
 * @magentoAppArea adminhtml
 */
class CustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Customer
     */
    protected $_model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $attributeValues;

    /**
     * @var array
     */
    private $attributeTypes;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->_model = $this->objectManager->create(Customer::class);
    }

    /**
     * Export "Customer Main File".
     *
     * @magentoDataFixture Magento/Customer/_files/import_export/customers.php
     */
    public function testExport()
    {
        /** @var Collection $collection */
        $collection = $this->objectManager->create(Collection::class);
        $this->initAttributeValues($collection);
        $this->initAttributeTypes($collection);

        $expectedAttributes = [];
        /** @var Attribute $attribute */
        foreach ($collection as $attribute) {
            $expectedAttributes[] = $attribute->getAttributeCode();
        }
        $expectedAttributes = array_diff($expectedAttributes, $this->_model->getDisabledAttributes());

        $this->_model->setWriter($this->objectManager->get(Csv::class));
        $data = $this->_model->export();
        $this->assertNotEmpty($data);

        $lines = $this->_csvToArray($data, 'email');

        $this->assertEquals(
            count($expectedAttributes),
            count(array_intersect($expectedAttributes, $lines['header'])),
            'Expected attribute codes were not exported.'
        );

        $this->assertNotEmpty($lines['data'], 'No data was exported.');

        /** @var CustomerModel[] $customers */
        $customers = $this->objectManager->create(CustomerCollection::class)->getItems();
        foreach ($customers as $customer) {
            $data = $this->processCustomerData($customer, $expectedAttributes);
            $exportData = $lines['data'][$data['email']];
            $exportData = $this->unsetDuplicateData($exportData);
            array_walk(
                $exportData,
                function (&$value) {
                    if (is_string($value) && $value === '') {
                        $value = null;
                    }
                }
            );

            $this->assertArraySubset($exportData, $data);
        }
    }

    /**
     * Initialize attribute option values.
     *
     * @param Collection $attributeCollection
     * @return $this
     */
    private function initAttributeValues(Collection $attributeCollection): CustomerTest
    {
        /** @var Attribute $attribute */
        foreach ($attributeCollection as $attribute) {
            $this->attributeValues[$attribute->getAttributeCode()] = $this->_model->getAttributeOptions($attribute);
        }

        return $this;
    }

    /**
     * Initialize attribute types.
     *
     * @param \Magento\Customer\Model\ResourceModel\Attribute\Collection $attributeCollection
     * @return $this
     */
    private function initAttributeTypes(Collection $attributeCollection): CustomerTest
    {
        /** @var Attribute $attribute */
        foreach ($attributeCollection as $attribute) {
            $this->attributeTypes[$attribute->getAttributeCode()] = $attribute->getFrontendInput();
        }

        return $this;
    }

    /**
     * Format Customer data as same as export data.
     *
     * @param CustomerModel $item
     * @param array $expectedAttributes
     * @return array
     */
    private function processCustomerData(CustomerModel $item, array $expectedAttributes): array
    {
        $data = [];
        foreach ($expectedAttributes as $attributeCode) {
            $attributeValue = $item->getData($attributeCode);

            if ($this->isMultiselect($attributeCode)) {
                $values = [];
                $attributeValue = explode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $attributeValue);
                foreach ($attributeValue as $value) {
                    $values[] = $this->getAttributeValueById($attributeCode, $value);
                }
                $data[$attributeCode] = implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $values);
            } else {
                $data[$attributeCode] = $this->getAttributeValueById($attributeCode, $attributeValue);
            }
        }

        return $data;
    }

    /**
     * Check that attribute is multiselect type by attribute code.
     *
     * @param string $attributeCode
     * @return bool
     */
    private function isMultiselect(string $attributeCode): bool
    {
        return isset($this->attributeTypes[$attributeCode])
            && $this->attributeTypes[$attributeCode] === 'multiselect';
    }

    /**
     * Return attribute value by id.
     *
     * @param string $attributeCode
     * @param int|string $valueId
     * @return mixed
     */
    private function getAttributeValueById(string $attributeCode, $valueId)
    {
        if (isset($this->attributeValues[$attributeCode])
            && isset($this->attributeValues[$attributeCode][$valueId])
        ) {
            return $this->attributeValues[$attributeCode][$valueId];
        }

        return $valueId;
    }

    /**
     * Unset non-useful or duplicate data from exported file data.
     *
     * @param array $data
     * @return array
     */
    private function unsetDuplicateData(array $data): array
    {
        unset($data['_website']);
        unset($data['_store']);
        unset($data['password']);

        return $data;
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
        $this->assertInstanceOf(Collection::class, $this->_model->getAttributeCollection());
    }

    /**
     * Test for method filterAttributeCollection()
     */
    public function testFilterAttributeCollection()
    {
        /** @var $collection Collection */
        $collection = $this->_model->getAttributeCollection();
        $collection = $this->_model->filterAttributeCollection($collection);
        /**
         * Check that disabled attributes is not existed in attribute collection
         */
        $existedAttributes = [];
        /** @var $attribute Attribute */
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
        /** @var $attribute Attribute */
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
        /**
         * Change created_at date of first customer for future filter test.
         */
        $customers = $this->objectManager->get(Registry::class)
            ->registry('_fixture/Magento_ImportExport_Customer_Collection');
        $customers[0]->setCreatedAt($createdAtDate);
        $customers[0]->save();
        /**
         * Change type of created_at attribute. In this case we have possibility to test date rage filter
         */
        $attributeCollection = $this->objectManager->create(Collection::class);
        $attributeCollection->addFieldToFilter('attribute_code', 'created_at');
        /** @var $createdAtAttribute Attribute */
        $createdAtAttribute = $attributeCollection->getFirstItem();
        $createdAtAttribute->setBackendType('datetime');
        $createdAtAttribute->save();
        /**
         * Prepare filter.asd
         */
        $parameters = [
            Export::FILTER_ELEMENT_GROUP => [
                'email' => 'example.com',
                'created_at' => [$createdAtDate, ''],
                'store_id' => $this->objectManager->get(StoreManagerInterface::class)->getStore()->getId()
            ]
        ];
        $this->_model->setParameters($parameters);
        /** @var $customers Collection */
        $collection = $this->_model->filterEntityCollection(
            $this->objectManager->create(
                CustomerCollection::class
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
