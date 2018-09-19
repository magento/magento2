<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Api;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests for cms block service.
 */
class BlockRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'cmsBlockRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/cmsBlock';

    /**
     * @var \Magento\Cms\Api\Data\BlockInterfaceFactory
     */
    protected $blockFactory;

    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Cms\Api\Data\BlockInterface|null
     */
    protected $currentBlock;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $this->blockFactory = Bootstrap::getObjectManager()->create(\Magento\Cms\Api\Data\BlockInterfaceFactory::class);
        $this->blockRepository = Bootstrap::getObjectManager()
            ->create(\Magento\Cms\Api\BlockRepositoryInterface::class);
        $this->dataObjectHelper = Bootstrap::getObjectManager()->create(\Magento\Framework\Api\DataObjectHelper::class);
        $this->dataObjectProcessor = Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Reflection\DataObjectProcessor::class);
    }

    /**
     * Clear temporary data
     */
    public function tearDown()
    {
        if ($this->currentBlock) {
            $this->blockRepository->delete($this->currentBlock);
            $this->currentBlock = null;
        }
    }

    /**
     * Test get \Magento\Cms\Api\Data\BlockInterface
     */
    public function testGet()
    {
        $blockTitle = 'Block title';
        $blockIdentifier = 'block-title';
        /** @var  \Magento\Cms\Api\Data\BlockInterface $blockDataObject */
        $blockDataObject = $this->blockFactory->create();
        $blockDataObject->setTitle($blockTitle)
            ->setIdentifier($blockIdentifier);
        $this->currentBlock = $this->blockRepository->save($blockDataObject);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $this->currentBlock->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetById',
            ],
        ];

        $block = $this->_webApiCall($serviceInfo, [BlockInterface::BLOCK_ID => $this->currentBlock->getId()]);
        $this->assertNotNull($block['id']);

        $blockData = $this->blockRepository->getById($block['id']);
        $this->assertEquals($blockData->getTitle(), $blockTitle);
        $this->assertEquals($blockData->getIdentifier(), $blockIdentifier);
    }

    /**
     * Test create \Magento\Cms\Api\Data\BlockInterface
     */
    public function testCreate()
    {
        $blockTitle = 'Block title';
        $blockIdentifier = 'block-title';
        /** @var  \Magento\Cms\Api\Data\BlockInterface $blockDataObject */
        $blockDataObject = $this->blockFactory->create();
        $blockDataObject->setTitle($blockTitle)
            ->setIdentifier($blockIdentifier);

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

        $requestData = ['block' => [
                BlockInterface::IDENTIFIER => $blockDataObject->getIdentifier(),
                BlockInterface::TITLE      => $blockDataObject->getTitle(),
            ],
        ];
        $block = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($block['id']);

        $this->currentBlock = $this->blockRepository->getById($block['id']);
        $this->assertEquals($this->currentBlock->getTitle(), $blockTitle);
        $this->assertEquals($this->currentBlock->getIdentifier(), $blockIdentifier);
    }

    /**
     * Test update \Magento\Cms\Api\Data\BlockInterface
     */
    public function testUpdate()
    {
        $blockTitle = 'Block title';
        $newBlockTitle = 'New Block title';
        $blockIdentifier = 'block-title';
        /** @var  \Magento\Cms\Api\Data\BlockInterface $blockDataObject */
        $blockDataObject = $this->blockFactory->create();
        $blockDataObject->setTitle($blockTitle)
            ->setIdentifier($blockIdentifier);
        $this->currentBlock = $this->blockRepository->save($blockDataObject);
        $this->dataObjectHelper->populateWithArray(
            $this->currentBlock,
            [BlockInterface::TITLE => $newBlockTitle],
            \Magento\Cms\Api\Data\BlockInterface::class
        );
        $blockData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->currentBlock,
            \Magento\Cms\Api\Data\BlockInterface::class
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

        $block = $this->_webApiCall($serviceInfo, ['block' => $blockData]);
        $this->assertNotNull($block['id']);

        $blockData = $this->blockRepository->getById($block['id']);
        $this->assertEquals($blockData->getTitle(), $newBlockTitle);
    }

    /**
     * Test delete \Magento\Cms\Api\Data\BlockInterface
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testDelete()
    {
        $blockTitle = 'Block title';
        $blockIdentifier = 'block-title';
        /** @var  \Magento\Cms\Api\Data\BlockInterface $blockDataObject */
        $blockDataObject = $this->blockFactory->create();
        $blockDataObject->setTitle($blockTitle)
            ->setIdentifier($blockIdentifier);
        $this->currentBlock = $this->blockRepository->save($blockDataObject);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $this->currentBlock->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

        $this->_webApiCall($serviceInfo, [BlockInterface::BLOCK_ID => $this->currentBlock->getId()]);
        $this->blockRepository->getById($this->currentBlock['id']);
    }

    /**
     * Test search \Magento\Cms\Api\Data\BlockInterface
     */
    public function testSearch()
    {
        $cmsBlocks = $this->prepareCmsBlocks();

        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = Bootstrap::getObjectManager()->create(FilterBuilder::class);

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()
            ->create(SearchCriteriaBuilder::class);

        $filter1 = $filterBuilder
            ->setField(BlockInterface::IDENTIFIER)
            ->setValue($cmsBlocks['first']->getIdentifier())
            ->create();
        $filter2 = $filterBuilder
            ->setField(BlockInterface::IDENTIFIER)
            ->setValue($cmsBlocks['third']->getIdentifier())
            ->create();
        $filter3 = $filterBuilder
            ->setField(BlockInterface::TITLE)
            ->setValue($cmsBlocks['second']->getTitle())
            ->create();
        $filter4 = $filterBuilder
            ->setField(BlockInterface::IS_ACTIVE)
            ->setValue(true)
            ->create();

        $searchCriteriaBuilder->addFilters([$filter1, $filter2]);
        $searchCriteriaBuilder->addFilters([$filter3, $filter4]);

        /** @var SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = Bootstrap::getObjectManager()->create(SortOrderBuilder::class);

        /** @var SortOrder $sortOrder */
        $sortOrder = $sortOrderBuilder->setField(BlockInterface::IDENTIFIER)
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
            $searchResult['items'][0][BlockInterface::IDENTIFIER],
            $cmsBlocks['third']->getIdentifier()
        );
    }

    /**
     * @return BlockInterface[]
     */
    private function prepareCmsBlocks()
    {
        $result = [];

        $blocksData['first'][BlockInterface::TITLE] = 'Block title 1';
        $blocksData['first'][BlockInterface::IDENTIFIER] = 'block-title-1' . uniqid();
        $blocksData['first'][BlockInterface::IS_ACTIVE] = true;

        $blocksData['second'][BlockInterface::TITLE] = 'Block title 2';
        $blocksData['second'][BlockInterface::IDENTIFIER] = 'block-title-2' . uniqid();
        $blocksData['second'][BlockInterface::IS_ACTIVE] = false;

        $blocksData['third'][BlockInterface::TITLE] = 'Block title 3';
        $blocksData['third'][BlockInterface::IDENTIFIER] = 'block-title-3' . uniqid();
        $blocksData['third'][BlockInterface::IS_ACTIVE] = true;

        foreach ($blocksData as $key => $blockData) {
            /** @var  \Magento\Cms\Api\Data\BlockInterface $blockDataObject */
            $blockDataObject = $this->blockFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $blockDataObject,
                $blockData,
                \Magento\Cms\Api\Data\BlockInterface::class
            );
            $result[$key] = $this->blockRepository->save($blockDataObject);
        }

        return $result;
    }
}
