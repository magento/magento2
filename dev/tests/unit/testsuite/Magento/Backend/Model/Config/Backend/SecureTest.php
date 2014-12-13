<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Model\Config\Backend;

class SecureTest extends \PHPUnit_Framework_TestCase
{
    public function testSaveMergedJsCssMustBeCleaned()
    {
        $eventDispatcher = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $appState = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $cacheManager = $this->getMock('Magento\Framework\App\CacheInterface');
        $logger = $this->getMock('Magento\Framework\Logger', [], [], '', false);
        $actionValidatorMock = $this->getMock(
            '\Magento\Framework\Model\ActionValidator\RemoveAction',
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
            'Magento\Backend\Model\Config\Backend\Secure',
            ['getOldValue'],
            [$context, $coreRegistry, $coreConfig, $mergeService, $resource, $resourceCollection]
        );
        $mergeService->expects($this->once())->method('cleanMergedJsCss');

        $model->setValue('new_value');
        $model->afterSave();
    }
}
