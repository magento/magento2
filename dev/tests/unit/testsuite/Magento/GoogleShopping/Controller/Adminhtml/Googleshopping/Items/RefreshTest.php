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

namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class RefreshTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items\Refresh
     */
    protected $controller;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\GoogleShopping\Model\Flag
     */
    protected $flag;

    /**
     * @var array
     */
    protected $controllerArguments;

    /**
     * @var \Magento\Framework\Notification\NotifierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $notificationInterface;

    protected function setUp()
    {
        $this->notificationInterface = $this->getMock('Magento\Framework\Notification\NotifierInterface');
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->controllerArguments = $this->objectManagerHelper->getConstructArguments(
            'Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items\Refresh',
            [
                'notifier' => $this->notificationInterface
            ]
        );

        $this->flag = $this->getMockBuilder('Magento\GoogleShopping\Model\Flag')->disableOriginalConstructor()
            ->setMethods(array('loadSelf', '__sleep', '__wakeup', 'isLocked', 'lock', 'unlock'))->getMock();
        $this->flag->expects($this->once())->method('loadSelf')->will($this->returnSelf());
        $this->flag->expects($this->once())->method('isLocked')->will($this->returnValue(false));
        $this->flag->expects($this->once())->method('unlock')->will($this->returnSelf());

        /** @var \PHPUnit_Framework_MockObject_MockObject $objectMananger */
        $objectMananger = $this->controllerArguments['context']->getObjectManager();
        $objectMananger->expects($this->at(0))->method('get')->with('Magento\GoogleShopping\Model\Flag')
            ->will($this->returnValue($this->flag));

        $this->controller = $this->objectManagerHelper->getObject(
            'Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items\Refresh',
            $this->controllerArguments
        );
    }

    public function testExecuteWithException()
    {
        $this->flag->expects($this->once())->method('lock')
            ->will($this->throwException(new \Exception('Test exception')));

        $logger = $this->getMockBuilder('Magento\Framework\Logger')->setMethods(array('logException'))
            ->disableOriginalConstructor()->getMock();
        $this->controllerArguments['context']->getObjectManager()->expects($this->at(1))->method('get')
            ->with('Magento\Framework\Logger')
            ->will($this->returnValue($logger));

        $this->controller->execute();
    }

    public function testExecute()
    {
        $massOperations = $this->getMockBuilder('Magento\GoogleShopping\Model\MassOperations')
            ->disableOriginalConstructor()->setMethods(array('setFlag', 'synchronizeItems'))->getMock();
        $massOperations->expects($this->once())->method('setFlag')->will($this->returnSelf());
        $massOperations->expects($this->once())->method('synchronizeItems')->will($this->returnSelf());

        $this->controllerArguments['context']->getObjectManager()->expects($this->once())->method('create')
            ->with('Magento\GoogleShopping\Model\MassOperations')
            ->will($this->returnValue($massOperations));

        $this->controller->execute();
    }
}
