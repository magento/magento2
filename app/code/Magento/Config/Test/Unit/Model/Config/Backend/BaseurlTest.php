<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Backend;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;

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

        $resource = $this->getMock('Magento\Config\Model\Resource\Config\Data', [], [], '', false);
        $resource->expects($this->any())->method('addCommitCallback')->will($this->returnValue($resource));
        $resourceCollection = $this->getMockBuilder('Magento\Framework\Data\Collection\AbstractDb')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mergeService = $this->getMock('Magento\Framework\View\Asset\MergeService', [], [], '', false);
        $coreRegistry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $coreConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $model = $this->getMock(
            'Magento\Config\Model\Config\Backend\Baseurl',
            ['getOldValue'],
            [$context, $coreRegistry, $coreConfig, $mergeService, $resource, $resourceCollection]
        );
        $mergeService->expects($this->once())->method('cleanMergedJsCss');

        $model->setValue('http://example.com/')->setPath(Store::XML_PATH_UNSECURE_BASE_URL);
        $model->afterSave();
    }
}
