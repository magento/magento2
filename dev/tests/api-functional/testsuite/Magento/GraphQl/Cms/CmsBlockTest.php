<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Cms;

use Magento\Cms\Model\Block;
use Magento\Cms\Model\GetBlockByIdentifier;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Widget\Model\Template\FilterEmulate;

class CmsBlockTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Verify the fields of CMS Block selected by identifiers
     *
     * @magentoApiDataFixture Magento/Cms/_files/block.php
     */
    public function testGetCmsBlocksByIdentifiers()
    {
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeId = (int)$storeManager->getStore()->getId();
        $cmsBlock = $this->objectManager->get(GetBlockByIdentifier::class)->execute("fixture_block", $storeId);
        $cmsBlockData = $cmsBlock->getData();
        /** @var FilterEmulate $widgetFilter */
        $widgetFilter = $this->objectManager->get(FilterEmulate::class);
        $renderedContent = $widgetFilter->setUseSessionInUrl(false)->filter($cmsBlock->getContent());
        $query =
            <<<QUERY
{
  cmsBlocks(identifiers: "fixture_block") {
    items {
      identifier
      title
      content
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('cmsBlocks', $response);
        $this->assertArrayHasKey('items', $response['cmsBlocks']);
        $this->assertArrayHasKey('content', $response['cmsBlocks']['items'][0]);
        $this->assertEquals($cmsBlockData['identifier'], $response['cmsBlocks']['items'][0]['identifier']);
        $this->assertEquals($cmsBlockData['title'], $response['cmsBlocks']['items'][0]['title']);
        $this->assertEquals($renderedContent, $response['cmsBlocks']['items'][0]['content']);
    }

    /**
     * Verify the message when CMS Block is disabled
     *
     * @magentoApiDataFixture Magento/Cms/_files/block.php
     */
    public function testGetDisabledCmsBlockByIdentifiers()
    {
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeId = (int)$storeManager->getStore()->getId();
        $cmsBlockId = $this->objectManager->get(GetBlockByIdentifier::class)
            ->execute("fixture_block", $storeId)
            ->getId();
        $this->objectManager->get(Block::class)->load($cmsBlockId)->setIsActive(0)->save();
        $query =
            <<<QUERY
{
  cmsBlocks(identifiers: "fixture_block") {
    items {
      identifier
      title
      content
    }
  }
}
QUERY;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No such entity.');
        $this->graphQlQuery($query);
    }

    /**
     * Verify the message when identifiers were not specified
     */
    public function testGetCmsBlockBypassingIdentifiers()
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

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('"identifiers" of CMS blocks should be specified');
        $this->graphQlQuery($query);
    }

    /**
     * Verify the message when CMS Block with such identifiers does not exist
     */
    public function testGetCmsBlockByNonExistentIdentifier()
    {
        $query =
            <<<QUERY
{
  cmsBlocks(identifiers: "0") {
    items {
      identifier
      title
      content
    }
  }
}
QUERY;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The CMS block with the "0" ID doesn\'t exist.');
        $this->graphQlQuery($query);
    }
}
