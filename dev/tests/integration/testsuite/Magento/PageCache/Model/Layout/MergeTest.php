<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Layout;

use Magento\Framework\View\EntitySpecificHandlesList;

class MergeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppArea frontend
     * @expectedException \LogicException
     * @expectedExceptionMessage Handle 'default' must not contain blocks with 'ttl' attribute specified
     */
    public function testLoadEntitySpecificHandleWithEsiBlock()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        // Mock cache to avoid layout being read from existing cache
        $cacheMock = $this->getMock(\Magento\Framework\Cache\FrontendInterface::class);
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
