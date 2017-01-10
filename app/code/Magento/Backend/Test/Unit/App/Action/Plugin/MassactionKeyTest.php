<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\App\Action\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\RequestInterface;

class MassactionKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Plugin\MassactionKey
     */
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RequestInterface
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AbstractAction
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->subjectMock = $this->getMock(\Magento\Backend\App\AbstractAction::class, [], [], '', false);
        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getPost', 'setPostValue']
        );

        $objectManager = new ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            \Magento\Backend\App\Action\Plugin\MassactionKey::class,
            [
                'subject' => $this->subjectMock,
                'request' => $this->requestMock,
            ]
        );
    }

    /**
     * @param $postData array|string
     * @param array $convertedData
     * @dataProvider beforeDispatchDataProvider
     */
    public function testBeforeDispatchWhenMassactionPrepareKeyRequestExists($postData, $convertedData)
    {
        $this->requestMock->expects($this->at(0))
            ->method('getPost')
            ->with('massaction_prepare_key')
            ->will($this->returnValue('key'));
        $this->requestMock->expects($this->at(1))
            ->method('getPost')
            ->with('key')
            ->will($this->returnValue($postData));
        $this->requestMock->expects($this->once())
            ->method('setPostValue')
            ->with('key', $convertedData);

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    public function beforeDispatchDataProvider()
    {
        return [
            'post_data_is_array' => [['key'], ['key']],
            'post_data_is_string' => ['key, key_two', ['key', ' key_two']]
        ];
    }

    public function testBeforeDispatchWhenMassactionPrepareKeyRequestNotExists()
    {
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('massaction_prepare_key')
            ->will($this->returnValue(false));
        $this->requestMock->expects($this->never())
            ->method('setPostValue');

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }
}
