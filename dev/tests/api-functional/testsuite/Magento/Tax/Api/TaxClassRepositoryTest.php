<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Api\Data\TaxClassDataBuilder;
use Magento\Tax\Model\ClassModelRegistry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

/**
 * Tests for tax class service.
 */
class TaxClassRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'taxTaxClassRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/taxClass';

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var TaxClassDataBuilder */
    private $taxClassBuilder;

    /** @var TaxClassRepositoryInterface */
    private $taxClassRepository;

    /** @var ClassModelRegistry */
    private $taxClassRegistry;

    const SAMPLE_TAX_CLASS_NAME = 'Wholesale Customer';

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Api\SearchCriteriaBuilder'
        );
        $this->filterBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Api\FilterBuilder'
        );
        $this->taxClassBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Tax\Api\Data\TaxClassDataBuilder'
        );
        $this->taxClassRegistry = Bootstrap::getObjectManager()->create(
            'Magento\Tax\Model\ClassModelRegistry'
        );
        $this->taxClassRepository = Bootstrap::getObjectManager()->create(
            'Magento\Tax\Model\TaxClass\Repository',
            ['classModelRegistry' => $this->taxClassRegistry]
        );
    }

    /**
     * Test create Data\TaxClassInterface
     */
    public function testCreateTaxClass()
    {
        $taxClassName = self::SAMPLE_TAX_CLASS_NAME . uniqid();
        /** @var  \Magento\Tax\Api\Data\TaxClassInterface $taxClassDataObject */
        $taxClassDataObject = $this->taxClassBuilder->setClassName($taxClassName)
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER)
            ->create();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $requestData = ['taxClass' => [
                'class_id' => $taxClassDataObject->getClassId(),
                'class_name' => $taxClassDataObject->getClassName(),
                'class_type' => $taxClassDataObject->getClassType(),
            ],
        ];
        $taxClassId = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($taxClassId);

        //Verify by getting the Data\TaxClassInterface
        $taxClassData = $this->taxClassRepository->get($taxClassId);
        $this->assertEquals($taxClassData->getClassName(), $taxClassName);
        $this->assertEquals($taxClassData->getClassType(), TaxClassManagementInterface::TYPE_CUSTOMER);
    }

    /**
     * Test create Data\TaxClassInterface
     */
    public function testUpdateTaxClass()
    {
        //Create Tax Class
        $taxClassDataObject = $this->taxClassBuilder->setClassName(self::SAMPLE_TAX_CLASS_NAME . uniqid())
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER)
            ->create();
        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);
        $this->assertNotNull($taxClassId);

        //Update Tax Class
        $updatedTaxClassName = self::SAMPLE_TAX_CLASS_NAME . uniqid();
        $updatedTaxClassDataObject = $this->taxClassBuilder
            ->populate($taxClassDataObject)
            ->setClassName($updatedTaxClassName)
            ->create();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $taxClassId,
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $taxClass = [
                'class_id' => $updatedTaxClassDataObject->getClassId(),
                'class_name' => $updatedTaxClassDataObject->getClassName(),
                'class_type' => $updatedTaxClassDataObject->getClassType(),
            ];

        $requestData = ['taxClass' => $taxClass, 'ClassId' => $taxClassId];

        $this->assertEquals($taxClassId, $this->_webApiCall($serviceInfo, $requestData));

        //Verify by getting the Data\TaxClassInterface
        $this->taxClassRegistry->remove($taxClassId);
        $taxClassData = $this->taxClassRepository->get($taxClassId);
        $this->assertEquals($taxClassData->getClassName(), $updatedTaxClassName);
    }

    public function testGetTaxClass()
    {
        //Create Tax Class
        $taxClassName = self::SAMPLE_TAX_CLASS_NAME . uniqid();
        $taxClassDataObject = $this->taxClassBuilder->setClassName($taxClassName)
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER)
            ->create();
        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);
        $this->assertNotNull($taxClassId);

        //Verify by getting the Data\TaxClassInterface
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $taxClassId,
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $requestData = ['taxClassId' => $taxClassId];
        $taxClassData = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($taxClassData[Data\TaxClassInterface::KEY_NAME], $taxClassName);
        $this->assertEquals(
            $taxClassData[Data\TaxClassInterface::KEY_TYPE],
            TaxClassManagementInterface::TYPE_CUSTOMER
        );
    }

    /**
     * Test delete Tax class
     */
    public function testDeleteTaxClass()
    {
        $taxClassDataObject = $this->taxClassBuilder->setClassName(self::SAMPLE_TAX_CLASS_NAME . uniqid())
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER)
            ->create();
        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);
        $this->assertNotNull($taxClassId);

        //Verify by getting the Data\TaxClassInterface
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $taxClassId,
                'httpMethod' => RestConfig::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];
        $requestData = ['taxClassId' => $taxClassId];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($result);

        try {
            $this->taxClassRegistry->remove($taxClassId);
            $this->taxClassRepository->get($taxClassId);
            $this->fail("Tax class was not expected to be returned after being deleted.");
        } catch (NoSuchEntityException $e) {
            $this->assertEquals('No such entity with class_id = ' . $taxClassId, $e->getMessage());
        }
    }

    /**
     * Test with a single filter
     */
    public function testSearchTaxClass()
    {
        $taxClassName = 'Retail Customer';
        $taxClassNameField = Data\TaxClassInterface::KEY_NAME;
        $filter = $this->filterBuilder->setField($taxClassNameField)
            ->setValue($taxClassName)
            ->create();
        $this->searchCriteriaBuilder->addFilter([$filter]);
        $searchData = $this->searchCriteriaBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData),
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(1, $searchResults['total_count']);
        $this->assertEquals($taxClassName, $searchResults['items'][0][$taxClassNameField]);
    }

    /**
     * Test using multiple filters
     */
    public function testSearchTaxClassMultipleFilterGroups()
    {
        $productTaxClass = [
            Data\TaxClassInterface::KEY_NAME => 'Taxable Goods',
            Data\TaxClassInterface::KEY_TYPE => 'PRODUCT',
        ];
        $customerTaxClass = [Data\TaxClassInterface::KEY_NAME => 'Retail Customer',
            Data\TaxClassInterface::KEY_TYPE => 'CUSTOMER', ];

        $filter1 = $this->filterBuilder->setField(Data\TaxClassInterface::KEY_NAME)
            ->setValue($productTaxClass[Data\TaxClassInterface::KEY_NAME])
            ->create();
        $filter2 = $this->filterBuilder->setField(Data\TaxClassInterface::KEY_NAME)
            ->setValue($customerTaxClass[Data\TaxClassInterface::KEY_NAME])
            ->create();
        $filter3 = $this->filterBuilder->setField(Data\TaxClassInterface::KEY_TYPE)
            ->setValue($productTaxClass[Data\TaxClassInterface::KEY_TYPE])
            ->create();
        $filter4 = $this->filterBuilder->setField(Data\TaxClassInterface::KEY_TYPE)
            ->setValue($customerTaxClass[Data\TaxClassInterface::KEY_TYPE])
            ->create();

        /**
         * (class_name == 'Retail Customer' || class_name == 'Taxable Goods)
         * && ( class_type == 'CUSTOMER' || class_type == 'PRODUCT')
         */
        $this->searchCriteriaBuilder->addFilter([$filter1, $filter2]);
        $this->searchCriteriaBuilder->addFilter([$filter3, $filter4]);
        $searchCriteria = $this->searchCriteriaBuilder->setCurrentPage(1)->setPageSize(10)->create();
        $searchData = $searchCriteria->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData),
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(2, $searchResults['total_count']);
        $this->assertEquals($productTaxClass[Data\TaxClassInterface::KEY_NAME],
            $searchResults['items'][0][Data\TaxClassInterface::KEY_NAME]);
        $this->assertEquals($customerTaxClass[Data\TaxClassInterface::KEY_NAME],
            $searchResults['items'][1][Data\TaxClassInterface::KEY_NAME]);

        /** class_name == 'Retail Customer' && ( class_type == 'CUSTOMER' || class_type == 'PRODUCT') */
        $this->searchCriteriaBuilder->addFilter([$filter2]);
        $this->searchCriteriaBuilder->addFilter([$filter3, $filter4]);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchData = $searchCriteria->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo['rest']['resourcePath'] = self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData);
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(1, $searchResults['total_count']);
        $this->assertEquals($customerTaxClass[Data\TaxClassInterface::KEY_NAME],
            $searchResults['items'][0][Data\TaxClassInterface::KEY_NAME]);
    }
}
