<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * @magentoAppArea adminhtml
 */
class PageTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\User\Model\User::class
        )->loadByUsername(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME
        );

        /** @var $session \Magento\Backend\Model\Auth\Session */
        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Backend\Model\Auth\Session::class
        );
        $session->setUser($user);
    }

    /**
     * Tests the get by identifier command
     * @param array $pageData
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @magentoDbIsolation enabled
     * @dataProvider testGetByIdentifierDataProvider
     */
    public function testGetByIdentifier(array $pageData)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Cms\Model\GetPageByIdentifier $getPageByIdentifierCommand */
        /** @var \Magento\Cms\Model\ResourceModel\Page $pageResource */
        /** @var \Magento\Cms\Model\PageFactory $pageFactory */
        $pageFactory = $objectManager->create(\Magento\Cms\Model\PageFactory::class);
        $pageResource = $objectManager->create(\Magento\Cms\Model\ResourceModel\Page::class);
        $getPageByIdentifierCommand = $objectManager->create(\Magento\Cms\Model\GetPageByIdentifier::class);

        # Prepare and save the temporary page
        $tempPage = $pageFactory->create();
        $tempPage->setData($pageData);
        $pageResource->save($tempPage);

        # Load previously created block and compare identifiers
        $storeId = reset($pageData['stores']);
        $page = $getPageByIdentifierCommand->execute($pageData['identifier'], $storeId);
        $this->assertEquals($pageData['identifier'], $page->getIdentifier());
    }

    /**
     * @param array $data
     * @param string $expectedIdentifier
     * @magentoDbIsolation enabled
     * @dataProvider generateIdentifierFromTitleDataProvider
     */
    public function testGenerateIdentifierFromTitle($data, $expectedIdentifier)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Cms\Model\Page $page */
        $page = $objectManager->create(\Magento\Cms\Model\Page::class);
        $page->setData($data);
        $page->save();
        $this->assertEquals($expectedIdentifier, $page->getIdentifier());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testUpdateTime()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /**
         * @var $db \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $db = $objectManager->get(\Magento\Framework\App\ResourceConnection::class)
            ->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        /** @var \Magento\Cms\Model\Page $page */
        $page = $objectManager->create(\Magento\Cms\Model\Page::class);
        $page->setData(['title' => 'Test', 'stores' => [1]]);
        $beforeTimestamp = $db->fetchCol('SELECT UNIX_TIMESTAMP()')[0];
        $page->save();
        $afterTimestamp = $db->fetchCol('SELECT UNIX_TIMESTAMP()')[0];
        $page = $objectManager->get(PageRepositoryInterface::class)->getById($page->getId());
        $pageTimestamp = strtotime($page->getUpdateTime());

        /*
         * These checks prevent a race condition MAGETWO-95534
         */
        $this->assertGreaterThanOrEqual($beforeTimestamp, $pageTimestamp);
        $this->assertLessThanOrEqual($afterTimestamp, $pageTimestamp);
    }

    public function generateIdentifierFromTitleDataProvider() : array
    {
        return [
            ['data' => ['title' => 'Test title', 'stores' => [1]], 'expectedIdentifier' => 'test-title'],
            [
                'data' => ['title' => 'Кирилический заголовок', 'stores' => [1]],
                'expectedIdentifier' => 'kirilicheskij-zagolovok'
            ],
            [
                'data' => ['title' => 'Test title', 'identifier' => 'custom-identifier', 'stores' => [1]],
                'expectedIdentifier' => 'custom-identifier'
            ]
        ];
    }

    /**
     * Data provider for "testGetByIdentifier" method
     * @return array
     */
    public function testGetByIdentifierDataProvider() : array
    {
        return [
            ['data' => [
                'title' => 'Test title',
                'identifier' => 'test-identifier',
                'page_layout' => '1column',
                'stores' => [1],
                'content' => 'Test content',
                'is_active' => 1
            ]]
        ];
    }
}
