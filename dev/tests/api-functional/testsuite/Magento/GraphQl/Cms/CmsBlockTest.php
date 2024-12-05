<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Cms;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * Get CMS Block test
 */
class CmsBlockTest extends GraphQlAbstract
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var FilterEmulate
     */
    private $filterEmulate;

    protected function setUp(): void
    {
        $this->blockRepository = Bootstrap::getObjectManager()->get(BlockRepositoryInterface::class);
        $this->filterEmulate = Bootstrap::getObjectManager()->get(FilterEmulate::class);
    }

    /**
     * Verify the fields of CMS Block selected by identifiers
     *
     * @magentoConfigFixture default_store web/seo/use_rewrites 1
     * @magentoApiDataFixture Magento/Cms/_files/blocks.php
     */
    public function testGetCmsBlock()
    {
        $cmsBlock = $this->blockRepository->getById('enabled_block');
        $cmsBlockData = $cmsBlock->getData();
        $renderedContent = $this->filterEmulate->filter($cmsBlock->getContent());

        $query =
            <<<QUERY
{
  cmsBlocks(identifiers: "enabled_block") {
    items {
      identifier
      title
      content
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('cmsBlocks', $response);
        self::assertArrayHasKey('items', $response['cmsBlocks']);

        self::assertEquals($cmsBlockData['identifier'], $response['cmsBlocks']['items'][0]['identifier']);
        self::assertEquals($cmsBlockData['title'], $response['cmsBlocks']['items'][0]['title']);
        self::assertEquals($renderedContent, $response['cmsBlocks']['items'][0]['content']);
    }

    /**
     * Verify the fields of CMS Block selected by block_id
     *
     * @magentoConfigFixture default_store web/seo/use_rewrites 1
     * @magentoApiDataFixture Magento/Cms/_files/blocks.php
     */
    public function testGetCmsBlockByBlockId()
    {
        $cmsBlock = $this->blockRepository->getById('enabled_block');
        $cmsBlockData = $cmsBlock->getData();
        $blockId = $cmsBlockData['block_id'];
        $renderedContent = $this->filterEmulate->filter($cmsBlock->getContent());

        $query =
            <<<QUERY
{
  cmsBlocks(identifiers: "$blockId") {
    items {
      identifier
      title
      content
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('cmsBlocks', $response);
        self::assertArrayHasKey('items', $response['cmsBlocks']);
        self::assertEquals($cmsBlockData['identifier'], $response['cmsBlocks']['items'][0]['identifier']);
        self::assertEquals($cmsBlockData['title'], $response['cmsBlocks']['items'][0]['title']);
        self::assertEquals($renderedContent, $response['cmsBlocks']['items'][0]['content']);
    }

    /**
     * Verify the message when CMS Block is disabled
     *
     *
     * @magentoApiDataFixture Magento/Cms/_files/blocks.php
     */
    public function testGetDisabledCmsBlock()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The CMS block with the "disabled_block" ID doesn\'t exist');

        $query =
            <<<QUERY
{
  cmsBlocks(identifiers: "disabled_block") {
    items {
      identifier
      title
      content
    }
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * Verify the message when identifiers were not specified
     *
     */
    public function testGetCmsBlocksWithoutIdentifiers()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('"identifiers" of CMS blocks should be specified');

        $query =
            <<<QUERY
{
  cmsBlocks(identifiers: []) {
    items {
      identifier
      title
      content
    }
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * Verify the message when CMS Block with such identifiers does not exist
     *
     */
    public function testGetCmsBlockByNonExistentIdentifier()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The CMS block with the "nonexistent_id" ID doesn\'t exist.');

        $query =
            <<<QUERY
{
  cmsBlocks(identifiers: "nonexistent_id") {
    items {
      identifier
      title
      content
    }
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * Verify the fields of CMS Block selected by identifiers
     *
     * @magentoApiDataFixture Magento/Cms/_files/blocks.php
     */
    public function testGetEnabledAndDisabledCmsBlockInOneRequest()
    {
        $query =
            <<<QUERY
{
  cmsBlocks(identifiers: ["enabled_block", "disabled_block"]) {
    items {
      identifier
    }
  }
}
QUERY;

        try {
            $this->graphQlQuery($query);
            self::fail('Response should contains errors.');
        } catch (ResponseContainsErrorsException $e) {
            $responseData = $e->getResponseData();
        }

        self::assertNotEmpty($responseData);
        self::assertEquals('enabled_block', $responseData['data']['cmsBlocks']['items'][0]['identifier']);
        self::assertEquals(
            'The CMS block with the "disabled_block" ID doesn\'t exist.',
            $responseData['errors'][0]['message']
        );
    }

    /**
     * Verify correct CMS block information per store
     *
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @magentoApiDataFixture Magento/Cms/_files/blocks_for_different_stores.php
     */
    public function testGetCmsBlockPerSpecificStore(): void
    {
        $blockIdentifier1 = 'test-block';
        $blockIdentifier2 = 'test-block-2';
        $secondStoreCode = 'second_store_view';
        $thirdStoreCode = 'third_store_view';

        //Verify the correct block information for second store is returned
        $cmsBlockResponseSecondStore = $this->getCmsBlockQuery($blockIdentifier1, $secondStoreCode);
        self::assertArrayHasKey('cmsBlocks', $cmsBlockResponseSecondStore);
        self::assertArrayHasKey('items', $cmsBlockResponseSecondStore['cmsBlocks']);
        self::assertEquals('test-block', $cmsBlockResponseSecondStore['cmsBlocks']['items'][0]['identifier']);
        self::assertEquals('Second store block', $cmsBlockResponseSecondStore['cmsBlocks']['items'][0]['title']);
        self::assertEquals('second_store_view', $cmsBlockResponseSecondStore['storeConfig']['code']);

        //Verify the correct block information for third store is returned
        $cmsBlockResponseThirdStore = $this->getCmsBlockQuery($blockIdentifier1, $thirdStoreCode);
        self::assertArrayHasKey('cmsBlocks', $cmsBlockResponseThirdStore);
        self::assertArrayHasKey('items', $cmsBlockResponseThirdStore['cmsBlocks']);
        self::assertEquals('test-block', $cmsBlockResponseThirdStore['cmsBlocks']['items'][0]['identifier']);
        self::assertEquals('Third store block', $cmsBlockResponseThirdStore['cmsBlocks']['items'][0]['title']);
        self::assertEquals('third_store_view', $cmsBlockResponseThirdStore['storeConfig']['code']);

        //Verify the correct block information for second block for second store is returned
        $cmsBlockResponseSecondStore = $this->getCmsBlockQuery($blockIdentifier2, $secondStoreCode);
        self::assertArrayHasKey('cmsBlocks', $cmsBlockResponseSecondStore);
        self::assertArrayHasKey('items', $cmsBlockResponseSecondStore['cmsBlocks']);
        self::assertEquals('test-block-2', $cmsBlockResponseSecondStore['cmsBlocks']['items'][0]['identifier']);
        self::assertEquals('Second store block 2', $cmsBlockResponseSecondStore['cmsBlocks']['items'][0]['title']);
        self::assertEquals('second_store_view', $cmsBlockResponseSecondStore['storeConfig']['code']);

        //Verify that exception is returned if block is not assigned to the store specified
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The CMS block with the "test-block-2" ID doesn\'t exist');

        $query =
            <<<QUERY
{
  cmsBlocks(identifiers: "$blockIdentifier2") {
    items {
      identifier
      title
      content
    }
  }
}
QUERY;
        $headerMap['Store'] = $thirdStoreCode;
        $this->graphQlQuery($query, [], '', $headerMap);
    }

    /**
     * Verify CMS block for a disabled store
     *
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @magentoApiDataFixture Magento/Cms/_files/blocks_for_different_stores.php
     */
    public function testGetCmsBlockForDisabledStore(): void
    {
        $blockIdentifier = 'test-block';
        $thirdStoreCode = 'third_store_view';
        $store = Bootstrap::getObjectManager()->get(Store::class);
        $store->load('third_store_view', 'code')->setIsActive(0)->save();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Requested store is not found');
        $this->getCmsBlockQuery($blockIdentifier, $thirdStoreCode);
    }

    /**
     * @magentoApiDataFixture Magento/Cms/_files/block_default_store.php
     */
    public function testGetCmsBlockAssignedToDefaultStore(): void
    {
        $blockIdentifier = 'default_store_block';
        $query = <<<QUERY
{
  cmsBlocks(identifiers: "$blockIdentifier") {
    items {
      identifier
      title
      content
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('cmsBlocks', $response);
        $this->assertCount(1, $response['cmsBlocks']['items']);
        $this->assertEquals($blockIdentifier, $response['cmsBlocks']['items'][0]['identifier']);
    }

    /**
     * Get cmsBlockQuery per store
     *
     * @param string $blockIdentifier
     * @param string $storeCode
     * @return array
     * @throws \Exception
     */
    private function getCmsBlockQuery($blockIdentifier, $storeCode): array
    {
        $query =
            <<<QUERY
{
  cmsBlocks(identifiers: "$blockIdentifier") {
    items {
      identifier
      title
      content
    }
  }
  storeConfig{code}
}
QUERY;
        $headerMap['Store'] = $storeCode;
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        return $response;
    }
}
