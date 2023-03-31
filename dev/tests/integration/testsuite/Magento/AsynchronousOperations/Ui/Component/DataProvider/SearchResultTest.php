<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Ui\Component\DataProvider;

use Magento\TestFramework\Helper\Bootstrap;

class SearchResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoDataFixture Magento/AsynchronousOperations/_files/bulk.php
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testGetAllIds()
    {
        $objectManager = Bootstrap::getObjectManager();
        $user = $objectManager->create(\Magento\User\Model\User::class);
        $user->loadByUsername(\Magento\TestFramework\Bootstrap::ADMIN_NAME);
        $session = $objectManager->get(\Magento\Backend\Model\Auth\Session::class);
        $session->setUser($user);

        /** @var \Magento\AsynchronousOperations\Ui\Component\DataProvider\SearchResult $searchResult */
        $searchResult = $objectManager->create(
            \Magento\AsynchronousOperations\Ui\Component\DataProvider\SearchResult::class
        );
        $this->assertEquals(6, $searchResult->getTotalCount());
    }
}
