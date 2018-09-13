<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Cms\Model\ResourceModel\Block;
use Magento\Cms\Model\BlockFactory;
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

        # Prepare and save the temporary block
        $beforeTimestamp = $this->objectManager->get(DateTime::class)->timestamp();
        $tempBlock       = $this->blockFactory->create();
        $tempBlock->setData($blockData);
        $this->blockResource->save($tempBlock);

        # Load previously created block and compare identifiers
        $storeId        = reset($blockData['stores']);
        $block          = $this->blockIdentifier->execute($blockData['identifier'], $storeId);
        $afterTimestamp = $this->objectManager->get(DateTime::class)->timestamp();
        $blockTimestamp = strtotime($block->getUpdateTime());

        /*
         * This test used to fail due to a race condition @see MAGETWO-87353
         * The DB time would be one second older than the check time. The new check allows the DB time
         * to be between the test start time and right before the assertion.
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
