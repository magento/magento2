<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Backend;

class BaseurlTest extends \PHPUnit_Framework_TestCase
{
    public function testSaveMergedJsCssMustBeCleaned()
    {
        $eventDispatcher = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $appState = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $cacheManager = $this->getMock('Magento\Framework\App\CacheInterface');
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $actionValidatorMock = $this->getMock(
            'Magento\Framework\Model\ActionValidator\RemoveAction',
            [],
            [],
            '',
            false
        );

        $context = new \Magento\Framework\Model\Context(
            $logger,
            $eventDispatcher,
            $cacheManager,
            $appState,
            $actionValidatorMock
        );

        $resource = $this->getMock('Magento\Core\Model\Resource\Config\Data', [], [], '', false);
        $resource->expects($this->any())->method('addCommitCallback')->will($this->returnValue($resource));
        $resourceCollection = $this->getMock('Magento\Framework\Data\Collection\Db', [], [], '', false);
        $mergeService = $this->getMock('Magento\Framework\View\Asset\MergeService', [], [], '', false);
        $coreRegistry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $coreConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $model = $this->getMock(
            'Magento\Backend\Model\Config\Backend\Baseurl',
            ['getOldValue'],
            [$context, $coreRegistry, $coreConfig, $mergeService, $resource, $resourceCollection]
        );
        $mergeService->expects($this->once())->method('cleanMergedJsCss');

        $model->setValue('http://example.com/')->setPath(\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL);
        $model->afterSave();
    }
}
