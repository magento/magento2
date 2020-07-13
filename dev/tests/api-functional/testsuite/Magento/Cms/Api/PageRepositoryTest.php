<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Api;

use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\Rules;
use Magento\Authorization\Model\RulesFactory;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
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
     * @var array
     */
    private $createdPages = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->pageFactory = Bootstrap::getObjectManager()->create(PageInterfaceFactory::class);
        $this->pageRepository = Bootstrap::getObjectManager()->create(PageRepositoryInterface::class);
        $this->dataObjectHelper = Bootstrap::getObjectManager()->create(DataObjectHelper::class);
        $this->dataObjectProcessor = Bootstrap::getObjectManager()->create(DataObjectProcessor::class);
        $this->roleFactory = Bootstrap::getObjectManager()->get(RoleFactory::class);
        $this->rulesFactory = Bootstrap::getObjectManager()->get(RulesFactory::class);
        $this->adminTokens = Bootstrap::getObjectManager()->get(AdminTokenServiceInterface::class);
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
            $this->pageRepository->delete($page);
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

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $this->currentPage->getId(),
                'httpMethod' => Request::HTTP_METHOD_GET,
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

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

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
     * Test update \Magento\Cms\Api\Data\PageInterface
     */
    public function testUpdate()
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

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
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

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $pageId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

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
     * Test delete \Magento\Cms\Api\Data\PageInterface
     */
    public function testDelete()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

        $pageTitle = self::PAGE_TITLE;
        $pageIdentifier = self::PAGE_IDENTIFIER_PREFIX . uniqid();
        /** @var  PageInterface $pageDataObject */
        $pageDataObject = $this->pageFactory->create();
        $pageDataObject->setTitle($pageTitle)
            ->setIdentifier($pageIdentifier);
        $this->currentPage = $this->pageRepository->save($pageDataObject);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $this->currentPage->getId(),
                'httpMethod' => Request::HTTP_METHOD_DELETE,
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
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

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
     */
    public function testCreateSamePage()
    {
        $pageIdentifier = self::PAGE_IDENTIFIER_PREFIX . uniqid();

        $pageId = $this->createPageWithIdentifier($pageIdentifier);
        $this->deletePageByIdentifier($pageId);
        $id = $this->createPageWithIdentifier($pageIdentifier);
        $this->currentPage = $this->pageRepository->getById($id);
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
     * @return string
     */
    private function createPageWithIdentifier($identifier)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
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
    private function deletePageByIdentifier($pageId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $pageId,
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

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
        $rules->setResources(['Magento_Cms::page']);
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
        $rules = Bootstrap::getObjectManager()->create(Rules::class);
        $rules->setRoleId($role->getId());
        $rules->setResources(['Magento_Cms::page', 'Magento_Cms::save_design']);
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
        $rules = Bootstrap::getObjectManager()->create(Rules::class);
        $rules->setRoleId($role->getId());
        $rules->setResources(['Magento_Cms::page']);
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
}
