<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Cms\Model\ResourceModel\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;
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
     * @var GetBlockByIdentifier
     */
    private $blockIdentifier;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var BlockFactory $blockFactory */
        /** @var Block $blockResource */
        /** @var GetBlockByIdentifier $getBlockByIdentifierCommand */
        $this->blockResource   = $this->objectManager->create(Block::class);
        $this->blockFactory    = $this->objectManager->create(BlockFactory::class);
        $this->blockIdentifier = $this->objectManager->create(GetBlockByIdentifier::class);
    }

    /**
     * Tests the get by identifier command
     * @param array $blockData
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @magentoDbIsolation enabled
     * @dataProvider testGetByIdentifierDataProvider
     */
    public function testGetByIdentifier(array $blockData)
    {
        # Prepare and save the temporary block
        $tempBlock = $this->blockFactory->create();
        $tempBlock->setData($blockData);
        $this->blockResource->save($tempBlock);

        # Load previously created block and compare identifiers
        $storeId = reset($blockData['stores']);
        $block   = $this->blockIdentifier->execute($blockData['identifier'], $storeId);
        $this->assertEquals($blockData['identifier'], $block->getIdentifier());
    }

    /**
     * Tests the get by identifier command
     * @param array $blockData
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @magentoDbIsolation enabled
     * @dataProvider testGetByIdentifierDataProvider
     */
    public function testUpdateTime(array $blockData)
    {
        /**
         * @var $db \Magento\Framework\DB\Adapter\AdapterInterface
         */
        $db = $this->objectManager->get(\Magento\Framework\App\ResourceConnection::class)
            ->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        # Prepare and save the temporary block
        $tempBlock       = $this->blockFactory->create();
        $tempBlock->setData($blockData);
        $beforeTimestamp = $db->fetchCol('SELECT UNIX_TIMESTAMP()')[0];
        $this->blockResource->save($tempBlock);
        $afterTimestamp = $db->fetchCol('SELECT UNIX_TIMESTAMP()')[0];

        # Load previously created block and compare identifiers
        $storeId        = reset($blockData['stores']);
        $block          = $this->blockIdentifier->execute($blockData['identifier'], $storeId);
        $blockTimestamp = strtotime($block->getUpdateTime());

        /*
         * These checks prevent a race condition MAGETWO-87353
         */
        $this->assertGreaterThanOrEqual($beforeTimestamp, $blockTimestamp);
        $this->assertLessThanOrEqual($afterTimestamp, $blockTimestamp);
    }

    /**
     * Data provider for "testGetByIdentifier" and "testUpdateTime" method
     * @return array
     */
    public function testGetByIdentifierDataProvider(): array
    {
        return [
            [
                'data' => [
                    'title'      => 'Test title',
                    'stores'     => [0],
                    'identifier' => 'test-identifier',
                    'content'    => 'Test content',
                    'is_active'  => 1
                ]
            ]
        ];
    }
}
