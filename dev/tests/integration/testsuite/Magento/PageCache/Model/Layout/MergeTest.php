<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Layout;

use Magento\Framework\View\EntitySpecificHandlesList;

class MergeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppArea frontend
     *
     */
    public function testLoadEntitySpecificHandleWithEsiBlock()
    {
        $this->expectExceptionMessage("Handle 'default' must not contain blocks with 'ttl' attribute specified");
        $this->expectException(\LogicException::class);
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        // Mock cache to avoid layout being read from existing cache
        $cacheMock = $this->createMock(\Magento\Framework\Cache\FrontendInterface::class);
        /** @var \Magento\Framework\View\Model\Layout\Merge $layoutMerge */
        $layoutMerge = $objectManager->create(
            \Magento\Framework\View\Model\Layout\Merge::class,
            ['cache' => $cacheMock]
        );

        /** @var EntitySpecificHandlesList $entitySpecificHandleList */
        $entitySpecificHandleList = $objectManager->get(EntitySpecificHandlesList::class);
        // Add 'default' handle, which has declarations of blocks with ttl, to the list of entity specific handles.
        // This allows to simulate a situation, when block with ttl attribute
        // is declared e.g. in 'catalog_product_view_id_1' handle
        $entitySpecificHandleList->addHandle('default');
        $layoutMerge->load(['default']);
    }
}
