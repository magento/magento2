<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\App\Action\Plugin;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Plugin\MassactionKey;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MassactionKeyTest extends TestCase
{
    /**
     * @var MassactionKey
     */
    protected $plugin;

    /**
     * @var MockObject|RequestInterface
     */
    protected $requestMock;

    /**
     * @var MockObject|AbstractAction
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->subjectMock = $this->createMock(AbstractAction::class);
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
            MassactionKey::class,
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
            ->willReturn('key');
        $this->requestMock->expects($this->at(1))
            ->method('getPost')
            ->with('key')
            ->willReturn($postData);
        $this->requestMock->expects($this->once())
            ->method('setPostValue')
            ->with('key', $convertedData);

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    /**
     * @return array
     */
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
            ->willReturn(false);
        $this->requestMock->expects($this->never())
            ->method('setPostValue');

        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }
}
