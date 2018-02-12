<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

/**
 * @magentoAppArea adminhtml
 */
class BlockTest extends \PHPUnit\Framework\TestCase
{
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
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Cms\Model\BlockFactory $blockFactory */
        /** @var \Magento\Cms\Model\ResourceModel\Block $blockResource */
        /** @var \Magento\Cms\Model\GetBlockByIdentifier $getBlockByIdentifierCommand */
        $blockResource = $objectManager->create(\Magento\Cms\Model\ResourceModel\Block::class);
        $blockFactory = $objectManager->create(\Magento\Cms\Model\BlockFactory::class);
        $getBlockByIdentifierCommand = $objectManager->create(\Magento\Cms\Model\GetBlockByIdentifier::class);

        # Prepare and save the temporary block
        $tempBlock = $blockFactory->create();
        $tempBlock->setData($blockData);
        $blockResource->save($tempBlock);

        # Load previously created block and compare identifiers
        $storeId = reset($blockData['stores']);
        $block = $getBlockByIdentifierCommand->execute($blockData['identifier'], $storeId);
        $this->assertEquals($blockData['identifier'], $block->getIdentifier());
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
                'stores' => [0],
                'identifier' => 'test-identifier',
                'content' => 'Test content',
                'is_active' => 1
            ]]
        ];
    }
}
