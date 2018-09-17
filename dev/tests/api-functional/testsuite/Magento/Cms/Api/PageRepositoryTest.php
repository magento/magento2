<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Api;

use Magento\Cms\Api\Data\PageInterface;
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
        $this->pageFactory = Bootstrap::getObjectManager()->create('Magento\Cms\Api\Data\PageInterfaceFactory');
        $this->pageRepository = Bootstrap::getObjectManager()->create('Magento\Cms\Api\PageRepositoryInterface');
        $this->dataObjectHelper = Bootstrap::getObjectManager()->create('Magento\Framework\Api\DataObjectHelper');
        $this->dataObjectProcessor = Bootstrap::getObjectManager()
            ->create('Magento\Framework\Reflection\DataObjectProcessor');
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
            'Magento\Cms\Api\Data\PageInterface'
        );
        $pageData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->currentPage,
            'Magento\Cms\Api\Data\PageInterface'
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
        $pageTitle = 'Page title';
        $pageIdentifier = 'page-title' . uniqid();
        /** @var  \Magento\Cms\Api\Data\PageInterface $pageDataObject */
        $pageDataObject = $this->pageFactory->create();
        $pageDataObject->setTitle($pageTitle)
            ->setIdentifier($pageIdentifier);
        $this->currentPage = $this->pageRepository->save($pageDataObject);

        $filterBuilder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\FilterBuilder');
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()
            ->create('Magento\Framework\Api\SearchCriteriaBuilder');
        $filter = $filterBuilder
            ->setField(PageInterface::IDENTIFIER)
            ->setValue($pageIdentifier)
            ->create();
        $searchCriteriaBuilder->addFilters([$filter]);

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
        $this->assertEquals(1, $searchResult['total_count']);
        $this->assertEquals($searchResult['items'][0][PageInterface::IDENTIFIER], $pageIdentifier);
    }

    /**
     * Create page with the same identifier after one was removed.
     */
    public function testCreateSamePage()
    {
        $pageIdentifier = 'page-' . uniqid();

        $pageId = $this->createPageWithIdentifier($pageIdentifier);
        $this->deletePageByIdentifier($pageId);
        $this->createPageWithIdentifier($pageIdentifier);
    }

    /**
     * Create page with hard-coded identifier to test with create-delete-create flow.
     * @param string $identifier
     * @return string
     */
    private function createPageWithIdentifier($identifier)
    {
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
        $requestData = ['page' =>
            [
                PageInterface::IDENTIFIER => $identifier,
                PageInterface::TITLE => 'Page title',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, $requestData);
        return $result['id'];
    }

    /**
     * Remove page with hard-coded-identifier
     * @param string $pageId
     * @return void
     */
    private function deletePageByIdentifier($pageId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $pageId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

        $this->_webApiCall($serviceInfo, [PageInterface::PAGE_ID => $pageId]);
    }
}
