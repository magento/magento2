<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Ui\Component\DataProvider\Operation\Failed;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class SearchResultTest
 */
class SearchResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoDataFixture Magento/AsynchronousOperations/_files/bulk.php
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testGetItems()
    {
        $objectManager = Bootstrap::getObjectManager();
        $request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);
        $requestData = [
            'uuid' => 'bulk-uuid-5',
        ];

        $request->setParams($requestData);

        /** @var \Magento\AsynchronousOperations\Ui\Component\DataProvider\SearchResult $searchResult */
        $searchResult = $objectManager->create(
            \Magento\AsynchronousOperations\Ui\Component\DataProvider\Operation\Failed\SearchResult::class
        );
        $this->assertEquals(1, $searchResult->getTotalCount());
        $expected = $searchResult->getItems();
        $expectedItem = array_shift($expected);
        $this->assertEquals('Test', $expectedItem->getMetaInformation());
        $this->assertEquals('5', $expectedItem->getEntityId());
    }
}
