<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\PageCache\Model\Layout;

use Magento\Framework\View\EntitySpecificHandlesList;

class MergeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppArea frontend
     */
    public function testLoadEntitySpecificHandleWithEsiBlock()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('must not contain blocks with \'ttl\' attribute specified');

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

        // Register test handle as entity-specific
        $testHandle = 'default';
        $entitySpecificHandleList->addHandle($testHandle);

        // Simple XML with ttl attribute that should trigger the validation
        $layoutXml = '<block class="Magento\Framework\View\Element\Template" name="test.block" ttl="3600"/>';

        // This should throw exception because the handle is entity-specific and contains ttl
        $layoutMerge->validateUpdate($testHandle, $layoutXml);
    }
}
