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
     * Tests the get by identifier functionality
     * @magentoDbIsolation enabled
     * @dataProvider testGetByIdentifierDataProvider
     * @param array $blockData
     */
    public function testGetByIdentifier(array $blockData)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Cms\Model\BlockFactory $blockFactory */
        /** @var \Magento\Cms\Model\ResourceModel\Block $blockResource */
        /** @var \Magento\Cms\Model\BlockManagment $blockManagment */
        $blockResource = $objectManager->create(\Magento\Cms\Model\ResourceModel\Block::class);
        $blockFactory = $objectManager->create(\Magento\Cms\Model\BlockFactory::class);
        $blockManagment = $objectManager->create(\Magento\Cms\Model\BlockManagment::class);

        # Prepare and save the temporary block
        $tempBlock = $blockFactory->create();
        $tempBlock->setData($blockData);
        $blockResource->save($tempBlock);

        # Load previously created block and compare identifiers
        $block = $blockManagment->getByIdentifier($blockData['identifier']);
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
                'stores' => [1],
                'identifier' => 'test-title',
                'content' => 'Test content',
                'is_active' => 1
            ]]
        ];

    }
}
