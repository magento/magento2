<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Cms;

use Magento\Cms\Api\BlockRepositoryInterface;
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

    protected function setUp()
    {
        $this->blockRepository = Bootstrap::getObjectManager()->get(BlockRepositoryInterface::class);
        $this->filterEmulate = Bootstrap::getObjectManager()->get(FilterEmulate::class);
    }

    /**
     * Verify the fields of CMS Block selected by identifiers
     *
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
     * @expectedException \Exception
     * @expectedExceptionMessage The CMS block with the "disabled_block" ID doesn't exist
     *
     * @magentoApiDataFixture Magento/Cms/_files/blocks.php
     */
    public function testGetDisabledCmsBlock()
    {
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
     * @expectedException \Exception
     * @expectedExceptionMessage "identifiers" of CMS blocks should be specified
     */
    public function testGetCmsBlocksWithoutIdentifiers()
    {
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
     * @expectedException \Exception
     * @expectedExceptionMessage The CMS block with the "nonexistent_id" ID doesn't exist.
     */
    public function testGetCmsBlockByNonExistentIdentifier()
    {
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
}
