<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\ResourceModel\Block;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class BlockTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Block
     */
    private $blockResource;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->blockResource   = $this->objectManager->create(Block::class);
        $this->blockFactory    = $this->objectManager->create(BlockFactory::class);
        $this->blockRepository = $this->objectManager->create(BlockRepositoryInterface::class);
    }

    /**
     * Tests update time.
     *
     * @param array $blockData
     * @return void
     * @magentoDbIsolation enabled
     * @dataProvider testUpdateTimeDataProvider
     */
    public function testUpdateTime(array $blockData)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $db */
        $db = $this->objectManager->get(ResourceConnection::class)
            ->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        # Prepare and save the temporary block
        $tempBlock = $this->blockFactory->create();
        $tempBlock->setData($blockData);
        $beforeTimestamp = $db->fetchOne('SELECT UNIX_TIMESTAMP()');
        $this->blockResource->save($tempBlock);
        $afterTimestamp = $db->fetchOne('SELECT UNIX_TIMESTAMP()');

        # Load previously created block and compare update_time field
        $block = $this->blockRepository->getById($tempBlock->getId());
        $blockTimestamp = strtotime($block->getUpdateTime());

        /** These checks prevent a race condition */
        $this->assertGreaterThanOrEqual($beforeTimestamp, $blockTimestamp);
        $this->assertLessThanOrEqual($afterTimestamp, $blockTimestamp);
    }

    /**
     * Data provider "testUpdateTime" method.
     *
     * @return array
     */
    public function testUpdateTimeDataProvider(): array
    {
        return [
            [
                'data' => [
                    'title'      => 'Test title',
                    'stores'     => [0],
                    'identifier' => 'test-identifier',
                    'content'    => 'Test content',
                    'is_active'  => 1,
                ],
            ],
        ];
    }
}
