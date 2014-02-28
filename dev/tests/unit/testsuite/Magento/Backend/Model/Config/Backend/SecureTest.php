<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Config\Backend;

class SecureTest extends \PHPUnit_Framework_TestCase
{
    public function testSaveMergedJsCssMustBeCleaned()
    {
        $eventDispatcher = $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false);
        $appState = $this->getMock('Magento\App\State', array(), array(), '', false);
        $storeManager = $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false);
        $cacheManager = $this->getMock('Magento\App\CacheInterface');
        $logger = $this->getMock('Magento\Logger', array(), array(), '', false);
        $context = new \Magento\Model\Context(
            $logger,
            $eventDispatcher,
            $cacheManager,
            $appState,
            $storeManager
        );

        $resource = $this->getMock('Magento\Core\Model\Resource\Config\Data', array(), array(), '', false);
        $resource->expects($this->any())
            ->method('addCommitCallback')
            ->will($this->returnValue($resource));
        $resourceCollection = $this->getMock('Magento\Data\Collection\Db', array(), array(), '', false);
        $mergeService = $this->getMock('Magento\View\Asset\MergeService', array(), array(), '', false);
        $coreRegistry = $this->getMock('Magento\Registry', array(), array(), '', false);
        $coreConfig = $this->getMock('Magento\App\ConfigInterface', array(), array(), '', false);
        $storeManager = $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false);

        $model = $this->getMock(
            'Magento\Backend\Model\Config\Backend\Secure',
            array('getOldValue'),
            array(
                $context,
                $coreRegistry,
                $storeManager,
                $coreConfig,
                $mergeService,
                $resource,
                $resourceCollection
            )
        );
        $mergeService->expects($this->once())
            ->method('cleanMergedJsCss');

        $model->setValue('new_value');
        $model->save();
    }
}
