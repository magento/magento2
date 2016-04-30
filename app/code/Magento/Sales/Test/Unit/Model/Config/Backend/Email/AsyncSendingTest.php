<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Config\Backend\Email;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test of backend model for global configuration value
 * 'sales_email/general/async_sending'.
 */
class AsyncSendingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Config\Backend\Email\AsyncSending
     */
    protected $object;

    /**
     * @var \Magento\Framework\App\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\Event\Manager\Proxy|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->config = $this->getMock('Magento\Framework\App\Config', [], [], '', false);

        $this->eventManager = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);

        $this->context = $this->getMock('Magento\Framework\Model\Context', ['getEventDispatcher'], [], '', false);
        $this->context->expects($this->any())->method('getEventDispatcher')->willReturn($this->eventManager);

        $this->object = $objectManager->getObject(
            '\Magento\Sales\Model\Config\Backend\Email\AsyncSending',
            [
                'config' => $this->config,
                'context' => $this->context
            ]
        );
    }

    /**
     * @param int $value
     * @param int $oldValue
     * @param string $eventName
     * @dataProvider afterSaveDataProvider
     * @return void
     */
    public function testAfterSave($value, $oldValue, $eventName)
    {
        $path = 'sales_email/general/async_sending';
        $scope = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        $this->object->setData(['value' => $value, 'path' => $path, 'scope' => $scope]);

        $this->config->expects($this->once())->method('getValue')->with($path, $scope)->willReturn($oldValue);

        if ($value == $oldValue) {
            $this->eventManager->expects($this->never())->method('dispatch');
        } else {
            $this->eventManager->expects($this->once())->method('dispatch')->with($eventName);
        }

        $this->object->afterSave();
    }

    /**
     * @return array
     */
    public function afterSaveDataProvider()
    {
        return [
            [0, 0, null],
            [1, 1, null],
            [0, 1, 'config_data_sales_email_general_async_sending_disabled'],
            [1, 0, 'config_data_sales_email_general_async_sending_enabled']
        ];
    }
}
