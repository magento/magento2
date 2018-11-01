<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\ResourceModel\Block;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
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

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var BlockFactory $blockFactory */
        /** @var Block $blockResource */
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
    {
        # Prepare and save the temporary block
        $tempBlock = $this->blockFactory->create();
        $tempBlock->setData($blockData);
        $this->blockResource->save($tempBlock);

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
