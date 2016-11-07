<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Tax\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Api\Data\TaxClassInterfaceFactory;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\ClassModelRegistry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests for tax class service.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxClassRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'taxTaxClassRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/taxClasses';

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var  SortOrderBuilder */
    private $sortOrderBuilder;

    /** @var TaxClassInterfaceFactory */
    private $taxClassFactory;

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
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->filterBuilder = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Api\FilterBuilder::class
        );
        $this->taxClassFactory = Bootstrap::getObjectManager()->create(
            \Magento\Tax\Api\Data\TaxClassInterfaceFactory::class
        );
        $this->taxClassRegistry = Bootstrap::getObjectManager()->create(
            \Magento\Tax\Model\ClassModelRegistry::class
        );
        $this->taxClassRepository = Bootstrap::getObjectManager()->create(
            \Magento\Tax\Model\TaxClass\Repository::class,
            ['classModelRegistry' => $this->taxClassRegistry]
        );
        $this->sortOrderBuilder = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Api\SortOrderBuilder::class
        );
    }

    /**
     * Test create Data\TaxClassInterface
     */
    public function testCreateTaxClass()
    {
        $taxClassName = self::SAMPLE_TAX_CLASS_NAME . uniqid();
        /** @var  \Magento\Tax\Api\Data\TaxClassInterface $taxClassDataObject */
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName($taxClassName)
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
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
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName(self::SAMPLE_TAX_CLASS_NAME . uniqid())
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER);
        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);
        $this->assertNotNull($taxClassId);

        //Update Tax Class
        $updatedTaxClassName = self::SAMPLE_TAX_CLASS_NAME . uniqid();
        $updatedTaxClassDataObject = $taxClassDataObject;
        $updatedTaxClassDataObject->setClassName($updatedTaxClassName);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $taxClassId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
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
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName($taxClassName)
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER);
        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);
        $this->assertNotNull($taxClassId);

        //Verify by getting the Data\TaxClassInterface
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $taxClassId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $requestData = ['taxClassId' => $taxClassId];
        $taxClassData = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($taxClassData[ClassModel::KEY_NAME], $taxClassName);
        $this->assertEquals(
            $taxClassData[ClassModel::KEY_TYPE],
            TaxClassManagementInterface::TYPE_CUSTOMER
        );
    }

    /**
     * Test delete Tax class
     */
    public function testDeleteTaxClass()
    {
        $taxClassDataObject = $this->taxClassFactory->create();
        $taxClassDataObject->setClassName(self::SAMPLE_TAX_CLASS_NAME . uniqid())
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER);
        $taxClassId = $this->taxClassRepository->save($taxClassDataObject);
        $this->assertNotNull($taxClassId);

        //Verify by getting the Data\TaxClassInterface
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $taxClassId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
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
        $taxClassNameField = ClassModel::KEY_NAME;
        $filter = $this->filterBuilder->setField($taxClassNameField)
            ->setValue($taxClassName)
            ->create();
        $this->searchCriteriaBuilder->addFilters([$filter]);
        $searchData = $this->searchCriteriaBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
            ClassModel::KEY_NAME => 'Taxable Goods',
            ClassModel::KEY_TYPE => 'PRODUCT',
        ];
        $customerTaxClass = [ClassModel::KEY_NAME => 'Retail Customer',
            ClassModel::KEY_TYPE => 'CUSTOMER', ];

        $filter1 = $this->filterBuilder->setField(ClassModel::KEY_NAME)
            ->setValue($productTaxClass[ClassModel::KEY_NAME])
            ->create();
        $filter2 = $this->filterBuilder->setField(ClassModel::KEY_NAME)
            ->setValue($customerTaxClass[ClassModel::KEY_NAME])
            ->create();
        $filter3 = $this->filterBuilder->setField(ClassModel::KEY_TYPE)
            ->setValue($productTaxClass[ClassModel::KEY_TYPE])
            ->create();
        $filter4 = $this->filterBuilder->setField(ClassModel::KEY_TYPE)
            ->setValue($customerTaxClass[ClassModel::KEY_TYPE])
            ->create();
        $sortOrder = $this->sortOrderBuilder->setField("class_type")
            ->setDirection("ASC")->create();

        /**
         * (class_name == 'Retail Customer' || class_name == 'Taxable Goods)
         * && ( class_type == 'CUSTOMER' || class_type == 'PRODUCT')
         */
        $this->searchCriteriaBuilder->addFilters([$filter1, $filter2]);
        $this->searchCriteriaBuilder->addFilters([$filter3, $filter4]);
        $this->searchCriteriaBuilder->addSortOrder($sortOrder);
        $searchCriteria = $this->searchCriteriaBuilder->setCurrentPage(1)->setPageSize(10)->create();
        $searchData = $searchCriteria->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(2, $searchResults['total_count']);

        $this->assertEquals(
            $customerTaxClass[ClassModel::KEY_NAME],
            $searchResults['items'][0][ClassModel::KEY_NAME]
        );
        $this->assertEquals(
            $productTaxClass[ClassModel::KEY_NAME],
            $searchResults['items'][1][ClassModel::KEY_NAME]
        );
        /** class_name == 'Retail Customer' && ( class_type == 'CUSTOMER' || class_type == 'PRODUCT') */
        $this->searchCriteriaBuilder->addFilters([$filter2]);
        $this->searchCriteriaBuilder->addFilters([$filter3, $filter4]);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchData = $searchCriteria->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo['rest']['resourcePath'] = self::RESOURCE_PATH . '/search' . '?' . http_build_query($requestData);
        $searchResults = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(1, $searchResults['total_count']);
        $this->assertEquals(
            $customerTaxClass[ClassModel::KEY_NAME],
            $searchResults['items'][0][ClassModel::KEY_NAME]
        );
    }
}
