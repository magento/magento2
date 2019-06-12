<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

<<<<<<< HEAD
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\ResourceModel\Block;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
=======
use Magento\Cms\Model\ResourceModel\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class BlockTest extends TestCase
{
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
     * @var BlockRepositoryInterface
     */
    private $blockRepository;
=======
     * @var GetBlockByIdentifier
     */
    private $blockIdentifier;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var BlockFactory $blockFactory */
        /** @var Block $blockResource */
<<<<<<< HEAD
        $this->blockResource   = $this->objectManager->create(Block::class);
        $this->blockFactory    = $this->objectManager->create(BlockFactory::class);
        $this->blockRepository = $this->objectManager->create(BlockRepositoryInterface::class);
    }

    /**
     * Test UpdateTime
     * @param array $blockData
     * @throws \Exception
     * @magentoDbIsolation enabled
     * @dataProvider testUpdateTimeDataProvider
     */
    public function testUpdateTime(array $blockData)
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        # Prepare and save the temporary block
        $tempBlock = $this->blockFactory->create();
        $tempBlock->setData($blockData);
        $this->blockResource->save($tempBlock);

<<<<<<< HEAD
        # Load previously created block and compare update_time field
        $block = $this->blockRepository->getById($tempBlock->getId());
        $date  = $this->objectManager->get(DateTime::class)->date();
        $this->assertEquals($date, $block->getUpdateTime());
    }

    /**
     * Data provider "testUpdateTime" method
     * @return array
     */
    public function testUpdateTimeDataProvider()
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
