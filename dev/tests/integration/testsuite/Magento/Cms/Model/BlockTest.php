<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

<<<<<<< HEAD
use Magento\Cms\Model\ResourceModel\Block;
use Magento\Cms\Model\BlockFactory;
=======
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\ResourceModel\Block;
>>>>>>> upstream/2.2-develop
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class BlockTest extends TestCase
{
<<<<<<< HEAD

=======
>>>>>>> upstream/2.2-develop
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
     * @var GetBlockByIdentifier
     */
    private $blockIdentifier;
=======
     * @var BlockRepositoryInterface
     */
    private $blockRepository;
>>>>>>> upstream/2.2-develop

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var BlockFactory $blockFactory */
        /** @var Block $blockResource */
<<<<<<< HEAD
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
=======
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
>>>>>>> upstream/2.2-develop
     */
    public function testUpdateTime(array $blockData)
    {
        # Prepare and save the temporary block
        $tempBlock = $this->blockFactory->create();
        $tempBlock->setData($blockData);
        $this->blockResource->save($tempBlock);

<<<<<<< HEAD
        # Load previously created block and compare identifiers
        $storeId = reset($blockData['stores']);
        $block   = $this->blockIdentifier->execute($blockData['identifier'], $storeId);
        $date    = $this->objectManager->get(DateTime::class)->date();
        $this->markTestIncomplete('MAGETWO-87353: \Magento\Cms\Model\BlockTest::testUpdateTime randomly fails on CI. '
            . 'Invalid assertion. Application node timestamp may significantly differ from DB node.');
=======
        # Load previously created block and compare update_time field
        $block = $this->blockRepository->getById($tempBlock->getId());
        $date  = $this->objectManager->get(DateTime::class)->date();
>>>>>>> upstream/2.2-develop
        $this->assertEquals($date, $block->getUpdateTime());
    }

    /**
<<<<<<< HEAD
     * Data provider for "testGetByIdentifier" and "testUpdateTime" method
     * @return array
     */
    public function testGetByIdentifierDataProvider(): array
=======
     * Data provider "testUpdateTime" method
     * @return array
     */
    public function testUpdateTimeDataProvider()
>>>>>>> upstream/2.2-develop
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
