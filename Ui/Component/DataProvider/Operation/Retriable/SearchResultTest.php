<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Ui\Component\DataProvider\Operation\Retriable;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class SearchResultTest
 */
class SearchResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/AsynchronousOperations/_files/bulk.php
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testGetTotalCount()
    {
        $objectManager = Bootstrap::getObjectManager();
        $requestData = [
            'uuid' => 'bulk-uuid-5',
        ];
        $request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);
        $request->setParams($requestData);

        /**
         * @var \Magento\AsynchronousOperations\Ui\Component\DataProvider\Operation\Retriable\SearchResult $searchResult
         */
        $searchResult = $objectManager->create(
            \Magento\AsynchronousOperations\Ui\Component\DataProvider\Operation\Retriable\SearchResult::class
        );
        $this->assertEquals(1, $searchResult->getTotalCount());
    }
}
