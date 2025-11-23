<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Api;

use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\Rules;
use Magento\Authorization\Model\RulesFactory;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Cms\Ui\Component\DataProvider as CmsDataProvider;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests for cms page service.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageRepositoryTest extends WebapiAbstract
{
    private const PAGE_TITLE = 'Page title';
    private const PAGE_TITLE_NEW = 'Page title new';
    private const PAGE_CONTENT = '<h1>Some content</h1>';
    private const PAGE_IDENTIFIER_PREFIX = 'page-';

    private const SERVICE_NAME = 'cmsPageRepositoryV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/cmsPage';

    /**
     * @var PageInterfaceFactory
     */
    private $pageFactory;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var PageInterface|null
     */
    private $currentPage;

    /**
     * @var RoleFactory
     */
    private $roleFactory;

    /**
     * @var RulesFactory
     */
    private $rulesFactory;

    /**
     * @var AdminTokenServiceInterface
     */
    private $adminTokens;

    /**
     * @var PageInterface[]
     */
    private $createdPages = [];

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var CmsDataProvider
     */
    private $cmsUiDataProvider;

    /**
     * @var PageResource
     */
    private $pageResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->pageFactory = $this->objectManager->create(PageInterfaceFactory::class);
        $this->pageRepository = $this->objectManager->create(PageRepositoryInterface::class);
        $this->dataObjectHelper = $this->objectManager->create(DataObjectHelper::class);
        $this->dataObjectProcessor = $this->objectManager->create(DataObjectProcessor::class);
        $this->roleFactory = $this->objectManager->get(RoleFactory::class);
        $this->rulesFactory = $this->objectManager->get(RulesFactory::class);
        $this->adminTokens = $this->objectManager->get(AdminTokenServiceInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->filterBuilder = $this->objectManager->get(FilterBuilder::class);
        $this->cmsUiDataProvider = $this->objectManager->create(
            CmsDataProvider::class,
            [
                'name' => 'cms_page_listing_data_source',
                'primaryFieldName' => 'page_id',
                'requestFieldName' => 'id',
            ]
        );
        $this->pageResource = $this->objectManager->get(PageResource::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->currentPage) {
            $this->pageRepository->delete($this->currentPage);
            $this->currentPage = null;
        }

        foreach ($this->createdPages as $page) {
            if ($page->getId()) {
                $this->pageRepository->delete($page);
            }
        }
    }

    /**
     * Test get page
     *
     * @return void
     */
    public function testGet(): void
    {
        $pageTitle = self::PAGE_TITLE;
        $pageIdentifier = self::PAGE_IDENTIFIER_PREFIX . uniqid();
        /** @var  PageInterface $pageDataObject */
        $pageDataObject = $this->pageFactory->create();
        $pageDataObject->setTitle($pageTitle)
            ->setIdentifier($pageIdentifier);
        $this->currentPage = $this->pageRepository->save($pageDataObject);

        $serviceInfo = $this->getServiceInfo(
            'GetById',
            Request::HTTP_METHOD_GET,
            self::RESOURCE_PATH . '/' . $this->currentPage->getId()
        );

        $page = $this->_webApiCall($serviceInfo, [PageInterface::PAGE_ID => $this->currentPage->getId()]);
        $this->assertNotNull($page['id']);

        $pageData = $this->pageRepository->getById($page['id']);
        $this->assertEquals($pageData->getTitle(), $pageTitle);
        $this->assertEquals($pageData->getIdentifier(), $pageIdentifier);
    }

    /**
     * @dataProvider byStoresProvider
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     * @param string $requestStore
     * @return void
     */
    public function testGetByStores(string $requestStore): void
    {
        $newStoreId = $this->getStoreIdByRequestStore($requestStore);
        $this->updatePage('page100', 0, ['store_id' => $newStoreId]);
        $page = $this->loadPageByIdentifier('page100', $newStoreId);
        $expectedData = array_intersect_key(
            $this->dataObjectProcessor->buildOutputDataArray($page, PageInterface::class),
            $this->getPageRequestData()['page']
        );
        $serviceInfo = $this->getServiceInfo(
            'GetById',
            Request::HTTP_METHOD_GET,
            self::RESOURCE_PATH . '/' . $page->getId()
        );
        $requestData = [];
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $requestData[PageInterface::PAGE_ID] = $page->getId();
        }

        $page = $this->_webApiCall($serviceInfo, $requestData, null, $requestStore);
        $this->assertResponseData($page, $expectedData);
    }

    /**
     * Test create page
     *
     * @return void
     */
    public function testCreate(): void
    {
        $pageTitle = self::PAGE_TITLE;
        $pageIdentifier = self::PAGE_IDENTIFIER_PREFIX . uniqid();
        /** @var  PageInterface $pageDataObject */
        $pageDataObject = $this->pageFactory->create();
        $pageDataObject->setTitle($pageTitle)
            ->setIdentifier($pageIdentifier);

        $serviceInfo = $this->getServiceInfo('Save', Request::HTTP_METHOD_POST);

        $requestData = [
            'page' => [
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
     * @dataProvider byStoresProvider
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     * @param string $requestStore
     * @return void
     */
    public function testCreateByStores(string $requestStore): void
    {
        $newStoreId = $this->getStoreIdByRequestStore($requestStore);
        $serviceInfo = $this->getServiceInfo('Save', Request::HTTP_METHOD_POST);
        $requestData = $this->getPageRequestData();

        $page = $this->_webApiCall($serviceInfo, $requestData, null, $requestStore);
        $this->createdPages[] = $this->loadPageByIdentifier(
            $requestData['page'][PageInterface::IDENTIFIER],
            $newStoreId
        );
        $this->assertResponseData($page, $requestData['page']);
        $pageGridData = $this->getPageGridDataByStoreCode($requestStore);
        $this->assertTrue(
            $this->isPageInArray($pageGridData['items'], $page['id']),
            sprintf('The "%s" page is missing from the "%s" store', $page['title'], $requestStore)
        );
    }

    /**
     * Test update \Magento\Cms\Api\Data\PageInterface
     *
     * @return void
     */
    public function testUpdate(): void
    {
        $pageTitle = self::PAGE_TITLE;
        $newPageTitle = self::PAGE_TITLE_NEW;
        $pageIdentifier = self::PAGE_IDENTIFIER_PREFIX . uniqid();
        /** @var  PageInterface $pageDataObject */
        $pageDataObject = $this->pageFactory->create();
        $pageDataObject->setTitle($pageTitle)
            ->setIdentifier($pageIdentifier);
        $this->currentPage = $this->pageRepository->save($pageDataObject);
        $this->dataObjectHelper->populateWithArray(
            $this->currentPage,
            [PageInterface::TITLE => $newPageTitle],
            PageInterface::class
        );
        $pageData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->currentPage,
            PageInterface::class
        );

        $serviceInfo = $this->getServiceInfo(
            'Save',
            Request::HTTP_METHOD_POST
        );

        $page = $this->_webApiCall($serviceInfo, ['page' => $pageData]);
        $this->assertNotNull($page['id']);

        $pageData = $this->pageRepository->getById($page['id']);
        $this->assertEquals($pageData->getTitle(), $newPageTitle);
    }

    /**
     * Test update page one field
     *
     * @return void
     */
    public function testUpdateOneField(): void
    {
        $pageTitle = self::PAGE_TITLE;
        $content = self::PAGE_CONTENT;
        $newPageTitle = self::PAGE_TITLE_NEW;
        $pageIdentifier = self::PAGE_IDENTIFIER_PREFIX . uniqid();

        /** @var PageInterface $pageDataObject */
        $pageDataObject = $this->pageFactory->create();
        $pageDataObject->setTitle($pageTitle)
            ->setIdentifier($pageIdentifier)
            ->setContent($content);
        $this->currentPage = $this->pageRepository->save($pageDataObject);
        $pageId = $this->currentPage->getId();

        $serviceInfo = $this->getServiceInfo(
            'Save',
            Request::HTTP_METHOD_PUT,
            self::RESOURCE_PATH . '/' . $pageId
        );

        $data = [
            'page' => [
                'title' => $newPageTitle,
            ],
        ];

        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $data['page'] += [
                'id' => $pageId,
                'identifier' => $pageIdentifier,
            ];
        }

        $page = $this->_webApiCall($serviceInfo, $data);

        $this->assertArrayHasKey('title', $page);
        $this->assertEquals($page['title'], $newPageTitle);

        $this->assertArrayHasKey('content', $page);
        $this->assertEquals($page['content'], $content);
    }

    /**
     * @dataProvider byStoresProvider
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     * @param string $requestStore
     * @return void
     */
    public function testUpdateByStores(string $requestStore): void
    {
        $newStoreId = $this->getStoreIdByRequestStore($requestStore);
        $page = $this->updatePage('page100', 0, ['store_id' => $newStoreId]);
        $serviceInfo = $this->getServiceInfo(
            'Save',
            Request::HTTP_METHOD_PUT,
            self::RESOURCE_PATH . '/' . $page->getId()
        );
        $requestData = $this->getPageRequestData();

        $page = $this->_webApiCall($serviceInfo, $requestData, null, $requestStore);
        $this->createdPages[] = $this->loadPageByIdentifier(
            $requestData['page'][PageInterface::IDENTIFIER],
            $newStoreId
        );
        $this->assertResponseData($page, $requestData['page']);
        $pageGridData = $this->getPageGridDataByStoreCode($requestStore);
        $this->assertTrue(
            $this->isPageInArray($pageGridData['items'], $page['id']),
            sprintf('The "%s" page is missing from the "%s" store', $page['title'], $requestStore)
        );
    }

    /**
     * Test delete \Magento\Cms\Api\Data\PageInterface
     *
     * @return void
     */
    public function testDelete(): void
    {
        $this->expectException(NoSuchEntityException::class);

        $pageTitle = self::PAGE_TITLE;
        $pageIdentifier = self::PAGE_IDENTIFIER_PREFIX . uniqid();
        /** @var  PageInterface $pageDataObject */
        $pageDataObject = $this->pageFactory->create();
        $pageDataObject->setTitle($pageTitle)
            ->setIdentifier($pageIdentifier);
        $this->currentPage = $this->pageRepository->save($pageDataObject);

        $serviceInfo = $this->getServiceInfo(
            'DeleteById',
            Request::HTTP_METHOD_DELETE,
            self::RESOURCE_PATH . '/' . $this->currentPage->getId()
        );

        $this->_webApiCall($serviceInfo, [PageInterface::PAGE_ID => $this->currentPage->getId()]);
        $this->pageRepository->getById($this->currentPage['id']);
    }

    /**
     * @dataProvider byStoresProvider
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     * @param string $requestStore
     * @return void
     */
    public function testDeleteByStores(string $requestStore): void
    {
        $newStoreId = $this->getStoreIdByRequestStore($requestStore);
        $page = $this->updatePage('page100', 0, ['store_id' => $newStoreId]);
        $serviceInfo = $this->getServiceInfo(
            'DeleteById',
            Request::HTTP_METHOD_DELETE,
            self::RESOURCE_PATH . '/' . $page->getId()
        );
        $requestData = [];
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $requestData[PageInterface::PAGE_ID] = $page->getId();
        }

        $pageResponse = $this->_webApiCall($serviceInfo, $requestData, null, $requestStore);
        $this->assertTrue($pageResponse);
        $pageGridData = $this->getPageGridDataByStoreCode($requestStore);
        $this->assertFalse(
            $this->isPageInArray($pageGridData['items'], (int)$page->getId()),
            sprintf('The "%s" page should not be present on the "%s" store', $page->getTitle(), $requestStore)
        );
    }

    /**
     * Test search \Magento\Cms\Api\Data\PageInterface
     *
     * @return void
     */
    public function testSearch(): void
    {
        $cmsPages = $this->prepareCmsPages();

        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = $this->objectManager->create(FilterBuilder::class);

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager
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
        $sortOrderBuilder = $this->objectManager->create(SortOrderBuilder::class);

        /** @var SortOrder $sortOrder */
        $sortOrder = $sortOrderBuilder->setField(PageInterface::IDENTIFIER)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();

        $searchCriteriaBuilder->setSortOrders([$sortOrder]);

        $searchCriteriaBuilder->setPageSize(1);
        $searchCriteriaBuilder->setCurrentPage(2);

        $searchData = $searchCriteriaBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = $this->getServiceInfo(
            'GetList',
            Request::HTTP_METHOD_GET,
            self::RESOURCE_PATH . "/search" . '?' . http_build_query($requestData)
        );

        $searchResult = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(2, $searchResult['total_count']);
        $this->assertCount(1, $searchResult['items']);
        $this->assertEquals(
            $searchResult['items'][0][PageInterface::IDENTIFIER],
            $cmsPages['third']->getIdentifier()
        );
    }

    /**
     * Create page with the same identifier after one was removed.
     *
     * @return void
     */
    public function testCreateSamePage(): void
    {
        $pageIdentifier = self::PAGE_IDENTIFIER_PREFIX . uniqid();

        $pageId = $this->createPageWithIdentifier($pageIdentifier);
        $this->deletePageByIdentifier($pageId);
        $id = $this->createPageWithIdentifier($pageIdentifier);
        $this->currentPage = $this->pageRepository->getById($id);
    }

    /**
     * Get stores for CRUD operations
     *
     * @return array
     */
    public static function byStoresProvider(): array
    {
        return [
            'default_store' => [
                'requestStore' => 'default',
            ],
            'second_store' => [
                'requestStore' => 'fixture_second_store',
            ],
            'all' => [
                'requestStore' => 'all',
            ],
        ];
    }

    /**
     * @return PageInterface[]
     */
    private function prepareCmsPages(): array
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
            /** @var  PageInterface $pageDataObject */
            $pageDataObject = $this->pageFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $pageDataObject,
                $pageData,
                PageInterface::class
            );
            $this->createdPages[] = $result[$key] = $this->pageRepository->save($pageDataObject);
        }

        return $result;
    }

    /**
     * Create page with hard-coded identifier to test with create-delete-create flow.
     * @param string $identifier
     * @return int
     */
    private function createPageWithIdentifier($identifier): int
    {
        $serviceInfo = $this->getServiceInfo('Save', Request::HTTP_METHOD_POST);
        $requestData = [
            'page' => [
                PageInterface::IDENTIFIER => $identifier,
                PageInterface::TITLE => self::PAGE_TITLE,
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
    private function deletePageByIdentifier($pageId): void
    {
        $serviceInfo = $this->getServiceInfo(
            'DeleteById',
            Request::HTTP_METHOD_DELETE,
            self::RESOURCE_PATH . '/' . $pageId
        );

        $this->_webApiCall($serviceInfo, [PageInterface::PAGE_ID => $pageId]);
    }

    /**
     * Check that extra authorization is required for the design properties.
     *
     * @magentoApiDataFixture Magento/User/_files/user_with_custom_role.php
     * @throws \Throwable
     * @return void
     */
    public function testSaveDesign(): void
    {
        //Updating our admin user's role to allow saving pages but not their design settings.
        /** @var Role $role */
        $role = $this->roleFactory->create();
        $role->load('test_custom_role', 'role_name');
        /** @var Rules $rules */
        $rules = $this->rulesFactory->create();
        $rules->setRoleId($role->getId());
        $rules->setResources(['Magento_Cms::save']);
        $rules->saveRel();
        //Using the admin user with custom role.
        $token = $this->adminTokens->createAdminAccessToken(
            'customRoleUser',
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        $id = 'test-cms-page';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
                'token' => $token
            ],
        ];
        $requestData = [
            'page' => [
                PageInterface::IDENTIFIER => $id,
                PageInterface::TITLE => self::PAGE_TITLE,
                PageInterface::CUSTOM_THEME => 1
            ],
        ];

        //Creating new page with design settings.
        $exceptionMessage = null;
        try {
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\Throwable $exception) {
            if ($restResponse = json_decode($exception->getMessage(), true)) {
                //REST
                $exceptionMessage = $restResponse['message'];
            } else {
                //SOAP
                $exceptionMessage = $exception->getMessage();
            }
        }
        //We don't have the permissions.
        $this->assertEquals('You are not allowed to change CMS pages design settings', $exceptionMessage);

        //Updating the user role to allow access to design properties.
        /** @var Rules $rules */
        $rules = $this->objectManager->create(Rules::class);
        $rules->setRoleId($role->getId());
        $rules->setResources(['Magento_Cms::save', 'Magento_Cms::save_design']);
        $rules->saveRel();
        //Making the same request with design settings.
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertArrayHasKey('id', $result);
        //Page must be saved.
        $this->currentPage = $this->pageRepository->getById($result['id']);
        $this->assertEquals($id, $this->currentPage->getIdentifier());
        $this->assertEquals(1, $this->currentPage->getCustomTheme());
        $requestData['page']['id'] = $this->currentPage->getId();

        //Updating our role to remove design properties access.
        /** @var Rules $rules */
        $rules = $this->objectManager->create(Rules::class);
        $rules->setRoleId($role->getId());
        $rules->setResources(['Magento_Cms::save']);
        $rules->saveRel();
        //Updating the page but with the same design properties values.
        $result = $this->_webApiCall($serviceInfo, $requestData);
        //We haven't changed the design so operation is successful.
        $this->assertArrayHasKey('id', $result);
        //Changing a design property.
        $requestData['page'][PageInterface::CUSTOM_THEME] = 2;
        $exceptionMessage = null;
        try {
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\Throwable $exception) {
            if ($restResponse = json_decode($exception->getMessage(), true)) {
                //REST
                $exceptionMessage = $restResponse['message'];
            } else {
                //SOAP
                $exceptionMessage = $exception->getMessage();
            }
        }
        //We don't have permissions to do that.
        $this->assertEquals('You are not allowed to change CMS pages design settings', $exceptionMessage);
    }

    /**
     * Get service info array
     *
     * @param string $soapOperation
     * @param string $httpMethod
     * @param string $resourcePath
     * @return array
     */
    private function getServiceInfo(
        string $soapOperation,
        string $httpMethod,
        string $resourcePath = self::RESOURCE_PATH
    ): array {
        return [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => $httpMethod,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . $soapOperation,
            ],
        ];
    }

    /**
     * Check that the page is in the page grid data
     *
     * @param array $pageGridData
     * @param int $pageId
     * @return bool
     */
    private function isPageInArray(array $pageGridData, int $pageId): bool
    {
        $isPagePresent = false;
        foreach ($pageGridData as $pageData) {
            if ($pageData['page_id'] == $pageId) {
                $isPagePresent = true;
                break;
            }
        }

        return $isPagePresent;
    }

    /**
     * Update page with data
     *
     * @param string $pageIdentifier
     * @param int $storeId
     * @param array $pageData
     * @return PageInterface
     */
    private function updatePage(string $pageIdentifier, int $storeId, array $pageData): PageInterface
    {
        $page = $this->loadPageByIdentifier($pageIdentifier, $storeId);
        $page->addData($pageData);

        return $this->pageRepository->save($page);
    }

    /**
     * Get request data for create or update page
     *
     * @return array
     */
    private function getPageRequestData(): array
    {
        return [
            'page' => [
                PageInterface::IDENTIFIER   => self::PAGE_IDENTIFIER_PREFIX . uniqid(),
                PageInterface::TITLE        => self::PAGE_TITLE . uniqid(),
                'active'                    => true,
                PageInterface::PAGE_LAYOUT  => '1column',
                PageInterface::CONTENT      => self::PAGE_CONTENT,
            ]
        ];
    }

    /**
     * Get store id by request store code
     *
     * @param string $requestStoreCode
     * @return int
     */
    private function getStoreIdByRequestStore(string $requestStoreCode): int
    {
        $storeCode = $requestStoreCode === 'all' ? 'admin' : $requestStoreCode;
        $store = $this->storeManager->getStore($storeCode);

        return (int)$store->getId();
    }

    /**
     * Check that the response data is as expected
     *
     * @param array $page
     * @param array $expectedData
     * @return void
     */
    private function assertResponseData(array $page, array $expectedData): void
    {
        $this->assertNotNull($page['id']);
        $actualData = array_intersect_key($page, $expectedData);
        $this->assertEquals($expectedData, $actualData, 'Response data does not match expected.');
    }

    /**
     * Get page grid data of cms ui dataprovider filtering by store code
     *
     * @param string $requestStore
     * @return array
     */
    private function getPageGridDataByStoreCode(string $requestStore): array
    {
        if ($requestStore !== 'all') {
            $store = $this->storeManager->getStore($requestStore);
            $this->cmsUiDataProvider->addFilter(
                $this->filterBuilder->setField('store_id')->setValue($store->getId())->create()
            );
        }

        return $this->cmsUiDataProvider->getData();
    }

    /**
     * Load page by identifier and store id
     *
     * @param string $identifier
     * @param int $storeId
     * @return PageInterface
     */
    private function loadPageByIdentifier(string $identifier, int $storeId): PageInterface
    {
        $page = $this->pageFactory->create();
        $page->setStoreId($storeId);
        $this->pageResource->load($page, $identifier, PageInterface::IDENTIFIER);

        return $page;
    }
}
