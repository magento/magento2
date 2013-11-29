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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Controller\Adminhtml;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function testCleanMediaAction()
    {
        // Wire object with mocks
        $response = $this->getMock('Magento\App\Response\Http', array(), array(), '', false);
        $objectManager = $this->getMock('Magento\ObjectManager');
        $eventManager = $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false);
        $backendHelper = $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false);
        $session = $this->getMock('Magento\Adminhtml\Model\Session', array('addSuccess'), array(), '', false);
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $controller = $helper->getObject('Magento\Backend\Controller\Adminhtml\Cache', array(
                'objectManager' => $objectManager,
                'response' => $response,
                'helper' => $backendHelper,
                'eventManager' => $eventManager
            )
        );

        // Setup expectations
        $mergeService = $this->getMock('Magento\Core\Model\Page\Asset\MergeService', array(), array(), '', false);
        $mergeService->expects($this->once())
            ->method('cleanMergedJsCss');

        $session->expects($this->once())
            ->method('addSuccess')
            ->with('The JavaScript/CSS cache has been cleaned.');

        $valueMap = array(
            array('Magento\Core\Model\Page\Asset\MergeService', $mergeService),
            array('Magento\Adminhtml\Model\Session', $session),
        );
        $objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));

        $backendHelper->expects($this->once())
            ->method('getUrl')
            ->with('adminhtml/*')
            ->will($this->returnValue('redirect_url'));

        $response->expects($this->once())
            ->method('setRedirect')
            ->with('redirect_url');
        // Run
        $controller->cleanMediaAction();
    }
}
