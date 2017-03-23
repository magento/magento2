<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Api;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests for cms page service.
 */
class PageRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'cmsPageRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/cmsPage';

    /**
     * @var \Magento\Cms\Api\Data\PageInterfaceFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Cms\Api\Data\PageInterface|null
     */
    protected $currentPage;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $this->pageFactory = Bootstrap::getObjectManager()->create(\Magento\Cms\Api\Data\PageInterfaceFactory::class);
        $this->pageRepository = Bootstrap::getObjectManager()->create(\Magento\Cms\Api\PageRepositoryInterface::class);
        $this->dataObjectHelper = Bootstrap::getObjectManager()->create(\Magento\Framework\Api\DataObjectHelper::class);
        $this->dataObjectProcessor = Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Reflection\DataObjectProcessor::class);
    }

    /**
     * Clear temporary data
     */
    public function tearDown()
    {
        if ($this->currentPage) {
            $this->pageRepository->delete($this->currentPage);
            $this->currentPage = null;
        }
    }

    /**
     * Test get \Magento\Cms\Api\Data\PageInterface
     */
    public function testGet()
    {
        $pageTitle = 'Page title';
        $pageIdentifier = 'page-title' . uniqid();
        /** @var  \Magento\Cms\Api\Data\PageInterface $pageDataObject */
        $pageDataObject = $this->pageFactory->create();
        $pageDataObject->setTitle($pageTitle)
            ->setIdentifier($pageIdentifier);
        $this->currentPage = $this->pageRepository->save($pageDataObject);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $this->currentPage->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetById',
            ],
        ];

        $page = $this->_webApiCall($serviceInfo, [PageInterface::PAGE_ID => $this->currentPage->getId()]);
        $this->assertNotNull($page['id']);

        $pageData = $this->pageRepository->getById($page['id']);
        $this->assertEquals($pageData->getTitle(), $pageTitle);
        $this->assertEquals($pageData->getIdentifier(), $pageIdentifier);
    }

    /**
     * Test create \Magento\Cms\Api\Data\PageInterface
     */
    public function testCreate()
    {
        $pageTitle = 'Page title';
        $pageIdentifier = 'page-title' . uniqid();
        /** @var  \Magento\Cms\Api\Data\PageInterface $pageDataObject */
        $pageDataObject = $this->pageFactory->create();
        $pageDataObject->setTitle($pageTitle)
            ->setIdentifier($pageIdentifier);

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

        $requestData = ['page' => [
                PageInterface::IDENTIFIER => $pageDataObject->getIdentifier(),
                PageInterface::TITLE      => $pageDataObject->getTitle(),
            ],
        ];
        $page = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($page['id']);

        $this->currentPage = $this->pageRepository->getById($page['id']);
        $this->assertEquals($this->currentPage->getTitle(), $pageTitle);
        $this->assertEquals($this->currentPage->getIdentifier(), $pageIdentifier);
    }

    /**
     * Test update \Magento\Cms\Api\Data\PageInterface
     */
    public function testUpdate()
    {
        $pageTitle = 'Page title';
        $newPageTitle = 'New Page title';
        $pageIdentifier = 'page-title' . uniqid();
        /** @var  \Magento\Cms\Api\Data\PageInterface $pageDataObject */
        $pageDataObject = $this->pageFactory->create();
        $pageDataObject->setTitle($pageTitle)
            ->setIdentifier($pageIdentifier);
        $this->currentPage = $this->pageRepository->save($pageDataObject);
        $this->dataObjectHelper->populateWithArray(
            $this->currentPage,
            [PageInterface::TITLE => $newPageTitle],
            \Magento\Cms\Api\Data\PageInterface::class
        );
        $pageData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->currentPage,
            \Magento\Cms\Api\Data\PageInterface::class
        );

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

        $page = $this->_webApiCall($serviceInfo, ['page' => $pageData]);
        $this->assertNotNull($page['id']);

        $pageData = $this->pageRepository->getById($page['id']);
        $this->assertEquals($pageData->getTitle(), $newPageTitle);
    }

    /**
     * Test delete \Magento\Cms\Api\Data\PageInterface
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testDelete()
    {
        $pageTitle = 'Page title';
        $pageIdentifier = 'page-title' . uniqid();
        /** @var  \Magento\Cms\Api\Data\PageInterface $pageDataObject */
        $pageDataObject = $this->pageFactory->create();
        $pageDataObject->setTitle($pageTitle)
            ->setIdentifier($pageIdentifier);
        $this->currentPage = $this->pageRepository->save($pageDataObject);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $this->currentPage->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

        $this->_webApiCall($serviceInfo, [PageInterface::PAGE_ID => $this->currentPage->getId()]);
        $this->pageRepository->getById($this->currentPage['id']);
    }

    /**
     * Test search \Magento\Cms\Api\Data\PageInterface
     */
    public function testSearch()
    {
        $cmsPages = $this->prepareCmsPages();

        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = Bootstrap::getObjectManager()->create(FilterBuilder::class);

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()
            ->create(SearchCriteriaBuilder::class);

        $filter1 = $filterBuilder
            ->setField(PageInterface::IDENTIFIER)
            ->setValue($cmsPages['first']->getIdentifier())
            ->create();
        $filter2 = $filterBuilder
            ->setField(PageInterface::IDENTIFIER)
            ->setValue($cmsPages['third']->getIdentifier())
            ->create();

        $searchCriteriaBuilder->addFilters([$filter1, $filter2]);

        $filter3 = $filterBuilder
            ->setField(PageInterface::TITLE)
            ->setValue($cmsPages['second']->getTitle())
            ->create();
        $filter4 = $filterBuilder
            ->setField(PageInterface::IS_ACTIVE)
            ->setValue(true)
            ->create();

        $searchCriteriaBuilder->addFilters([$filter3, $filter4]);

        /** @var SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = Bootstrap::getObjectManager()->create(SortOrderBuilder::class);

        /** @var SortOrder $sortOrder */
        $sortOrder = $sortOrderBuilder->setField(PageInterface::IDENTIFIER)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();

        $searchCriteriaBuilder->setSortOrders([$sortOrder]);

        $searchCriteriaBuilder->setPageSize(1);
        $searchCriteriaBuilder->setCurrentPage(2);

        $searchData = $searchCriteriaBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/search" . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $searchResult = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(2, $searchResult['total_count']);
        $this->assertEquals(1, count($searchResult['items']));
        $this->assertEquals(
            $searchResult['items'][0][PageInterface::IDENTIFIER],
            $cmsPages['third']->getIdentifier()
        );
    }

    /**
     * @return PageInterface[]
     */
    private function prepareCmsPages()
    {
        $result = [];

        $pagesData['first'][PageInterface::TITLE] = 'Page title 1';
        $pagesData['first'][PageInterface::IDENTIFIER] = 'page-title-1' . uniqid();
        $pagesData['first'][PageInterface::IS_ACTIVE] = true;

        $pagesData['second'][PageInterface::TITLE] = 'Page title 2';
        $pagesData['second'][PageInterface::IDENTIFIER] = 'page-title-2' . uniqid();
        $pagesData['second'][PageInterface::IS_ACTIVE] = false;

        $pagesData['third'][PageInterface::TITLE] = 'Page title 3';
        $pagesData['third'][PageInterface::IDENTIFIER] = 'page-title-3' . uniqid();
        $pagesData['third'][PageInterface::IS_ACTIVE] = true;

        foreach ($pagesData as $key => $pageData) {
            /** @var  \Magento\Cms\Api\Data\PageInterface $pageDataObject */
            $pageDataObject = $this->pageFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $pageDataObject,
                $pageData,
                \Magento\Cms\Api\Data\PageInterface::class
            );
            $result[$key] = $this->pageRepository->save($pageDataObject);
        }

        return $result;
    }
}
