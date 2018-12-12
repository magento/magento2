<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * @magentoAppArea adminhtml
 */
class PageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $user = $this->objectManager->create(
            \Magento\User\Model\User::class
        )->loadByUsername(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME
        );

        /** @var $session \Magento\Backend\Model\Auth\Session */
        $session = $this->objectManager->get(
            \Magento\Backend\Model\Auth\Session::class
        );
        $session->setUser($user);
    }

    /**
     * @magentoDbIsolation enabled
     * @dataProvider       generateIdentifierFromTitleDataProvider
     * @param array  $data
     * @param string $expectedIdentifier
     * @return void
     */
    public function testGenerateIdentifierFromTitle(array $data, string $expectedIdentifier)
    {
        /** @var Page $page */
        $page = $this->objectManager->create(Page::class);
        $page->setData($data);
        $page->save();
        $this->assertEquals($expectedIdentifier, $page->getIdentifier());
    }

    /**
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testUpdateTime()
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $db */
        $db = $this->objectManager->get(ResourceConnection::class)
            ->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        /** @var Page $page */
        $page = $this->objectManager->create(Page::class);
        $page->setData(['title' => 'Test', 'stores' => [1]]);
        $beforeTimestamp = $db->fetchOne('SELECT UNIX_TIMESTAMP()');
        $page->save();
        $afterTimestamp = $db->fetchOne('SELECT UNIX_TIMESTAMP()');
        $page = $this->objectManager->get(PageRepositoryInterface::class)->getById($page->getId());
        $pageTimestamp = strtotime($page->getUpdateTime());

        /** These checks prevent a race condition */
        $this->assertGreaterThanOrEqual($beforeTimestamp, $pageTimestamp);
        $this->assertLessThanOrEqual($afterTimestamp, $pageTimestamp);
    }

    /**
     * @return array
     */
    public function generateIdentifierFromTitleDataProvider(): array
    {
        return [
            ['data' => ['title' => 'Test title', 'stores' => [1]], 'expectedIdentifier' => 'test-title'],
            [
                'data' => ['title' => 'Кирилический заголовок', 'stores' => [1]],
                'expectedIdentifier' => 'kirilicheskij-zagolovok',
            ],
            [
                'data' => ['title' => 'Test title', 'identifier' => 'custom-identifier', 'stores' => [1]],
                'expectedIdentifier' => 'custom-identifier',
            ],
        ];
    }
}
