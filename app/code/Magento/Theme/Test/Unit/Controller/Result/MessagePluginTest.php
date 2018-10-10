<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Result;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Theme\Controller\Result\MessagePlugin;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MessagePluginTest extends \PHPUnit\Framework\TestCase
{
    /** @var MessagePlugin */
    protected $model;

    /** @var CookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cookieManagerMock;

    /** @var CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $cookieMetadataFactoryMock;

    /** @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerMock;

    /** @var InterpretationStrategyInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $interpretationStrategyMock;

    /** @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject */
    private $serializerMock;

    /** @var InlineInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $inlineTranslateMock;

    protected function setUp()
    {
        $this->cookieManagerMock = $this->getMockBuilder(CookieManagerInterface::class)
            ->getMockForAbstractClass();
        $this->cookieMetadataFactoryMock = $this->getMockBuilder(CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->managerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->interpretationStrategyMock = $this->getMockBuilder(InterpretationStrategyInterface::class)
            ->getMockForAbstractClass();
        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->getMock();
        $this->inlineTranslateMock = $this->getMockBuilder(InlineInterface::class)->getMockForAbstractClass();

        $this->model = new MessagePlugin(
            $this->cookieManagerMock,
            $this->cookieMetadataFactoryMock,
            $this->managerMock,
            $this->interpretationStrategyMock,
            $this->serializerMock,
            $this->inlineTranslateMock
        );
    }

    public function testAfterRenderResultJson()
    {
        /** @var Json|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieManagerMock->expects($this->never())
            ->method('setPublicCookie');

        $this->assertEquals($resultMock, $this->model->afterRenderResult($resultMock, $resultMock));
    }

    public function testAfterRenderResult()
    {
        $existingMessages = [
            [
                'type' => 'message0type',
                'text' => 'message0text',
            ],
        ];
        $messageType = 'message1type';
        $messageText = 'message1text';
        $messages = [
            [
                'type' => $messageType,
                'text' => $messageText,
            ],
        ];
        $messages = array_merge($existingMessages, $messages);
        
        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PublicCookieMetadata|\PHPUnit_Framework_MockObject_MockObject $cookieMetadataMock */
        $cookieMetadataMock = $this->getMockBuilder(PublicCookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($cookieMetadataMock);

        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                MessagePlugin::MESSAGES_COOKIES_NAME,
                json_encode($messages),
                $cookieMetadataMock
            );
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(
                MessagePlugin::MESSAGES_COOKIES_NAME
            )
            ->willReturn(json_encode($existingMessages));

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturnCallback(
                function ($data) {
                    return json_decode($data, true);
                }
            );
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturnCallback(
                function ($data) {
                    return json_encode($data);
                }
            );

        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $messageMock */
        $messageMock = $this->getMockBuilder(MessageInterface::class)
            ->getMock();
        $messageMock->expects($this->once())
            ->method('getType')
            ->willReturn($messageType);

        $this->interpretationStrategyMock->expects($this->once())
            ->method('interpret')
            ->with($messageMock)
            ->willReturn($messageText);

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$messageMock]);

        $this->managerMock->expects($this->once())
            ->method('getMessages')
            ->with(true, null)
            ->willReturn($collectionMock);

        $this->assertEquals($resultMock, $this->model->afterRenderResult($resultMock, $resultMock));
    }

    public function testAfterRenderResultWithNoMessages()
    {
        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(
                MessagePlugin::MESSAGES_COOKIES_NAME
            )
            ->willReturn(json_encode([]));

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturnCallback(
                function ($data) {
                    return json_decode($data, true);
                }
            );
        $this->serializerMock->expects($this->never())
            ->method('serialize');

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $this->managerMock->expects($this->once())
            ->method('getMessages')
            ->with(true, null)
            ->willReturn($collectionMock);

        $this->cookieMetadataFactoryMock->expects($this->never())
            ->method('createPublicCookieMetadata')
            ->willReturn(null);

        $this->assertEquals($resultMock, $this->model->afterRenderResult($resultMock, $resultMock));
    }

    public function testAfterRenderResultWithoutExisting()
    {
        $messageType = 'message1type';
        $messageText = 'message1text';
        $messages = [
            [
                'type' => $messageType,
                'text' => $messageText,
            ],
        ];

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PublicCookieMetadata|\PHPUnit_Framework_MockObject_MockObject $cookieMetadataMock */
        $cookieMetadataMock = $this->getMockBuilder(PublicCookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($cookieMetadataMock);

        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                MessagePlugin::MESSAGES_COOKIES_NAME,
                json_encode($messages),
                $cookieMetadataMock
            );
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(
                MessagePlugin::MESSAGES_COOKIES_NAME
            )
            ->willReturn(json_encode([]));

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturnCallback(
                function ($data) {
                    return json_decode($data, true);
                }
            );
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturnCallback(
                function ($data) {
                    return json_encode($data);
                }
            );

        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $messageMock */
        $messageMock = $this->getMockBuilder(MessageInterface::class)
            ->getMock();
        $messageMock->expects($this->once())
            ->method('getType')
            ->willReturn($messageType);

        $this->interpretationStrategyMock->expects($this->once())
            ->method('interpret')
            ->with($messageMock)
            ->willReturn($messageText);

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$messageMock]);

        $this->managerMock->expects($this->once())
            ->method('getMessages')
            ->with(true, null)
            ->willReturn($collectionMock);

        $this->assertEquals($resultMock, $this->model->afterRenderResult($resultMock, $resultMock));
    }

    public function testAfterRenderResultWithWrongJson()
    {
        $messageType = 'message1type';
        $messageText = 'message1text';
        $messages = [
            [
                'type' => $messageType,
                'text' => $messageText,
            ],
        ];

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PublicCookieMetadata|\PHPUnit_Framework_MockObject_MockObject $cookieMetadataMock */
        $cookieMetadataMock = $this->getMockBuilder(PublicCookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($cookieMetadataMock);

        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                MessagePlugin::MESSAGES_COOKIES_NAME,
                json_encode($messages),
                $cookieMetadataMock
            );
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(
                MessagePlugin::MESSAGES_COOKIES_NAME
            )
            ->willReturn(null);

        $this->serializerMock->expects($this->never())
            ->method('unserialize');

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturnCallback(
                function ($data) {
                    return json_encode($data);
                }
            );

        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $messageMock */
        $messageMock = $this->getMockBuilder(MessageInterface::class)
            ->getMock();
        $messageMock->expects($this->once())
            ->method('getType')
            ->willReturn($messageType);

        $this->interpretationStrategyMock->expects($this->once())
            ->method('interpret')
            ->with($messageMock)
            ->willReturn($messageText);

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$messageMock]);

        $this->managerMock->expects($this->once())
            ->method('getMessages')
            ->with(true, null)
            ->willReturn($collectionMock);

        $this->assertEquals($resultMock, $this->model->afterRenderResult($resultMock, $resultMock));
    }

    public function testAfterRenderResultWithWrongArray()
    {
        $messageType = 'message1type';
        $messageText = 'message1text';
        $messages = [
            [
                'type' => $messageType,
                'text' => $messageText,
            ],
        ];

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PublicCookieMetadata|\PHPUnit_Framework_MockObject_MockObject $cookieMetadataMock */
        $cookieMetadataMock = $this->getMockBuilder(PublicCookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($cookieMetadataMock);

        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                MessagePlugin::MESSAGES_COOKIES_NAME,
                json_encode($messages),
                $cookieMetadataMock
            );
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(
                MessagePlugin::MESSAGES_COOKIES_NAME
            )
            ->willReturn(json_encode('string'));

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturnCallback(
                function ($data) {
                    return json_decode($data, true);
                }
            );
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturnCallback(
                function ($data) {
                    return json_encode($data);
                }
            );

        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $messageMock */
        $messageMock = $this->getMockBuilder(MessageInterface::class)
            ->getMock();
        $messageMock->expects($this->once())
            ->method('getType')
            ->willReturn($messageType);

        $this->interpretationStrategyMock->expects($this->once())
            ->method('interpret')
            ->with($messageMock)
            ->willReturn($messageText);

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$messageMock]);

        $this->managerMock->expects($this->once())
            ->method('getMessages')
            ->with(true, null)
            ->willReturn($collectionMock);

        $this->assertEquals($resultMock, $this->model->afterRenderResult($resultMock, $resultMock));
    }

    /**
     * @return void
     */
    public function testAfterRenderResultWithAllowedInlineTranslate()
    {
        $messageType = 'message1type';
        $messageText = '{{{message1text}}{{message1text}}{{message1text}}{{theme/luma}}}';
        $expectedMessages = [
            [
                'type' => $messageType,
                'text' => 'message1text',
            ],
        ];

        /** @var Redirect|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PublicCookieMetadata|\PHPUnit_Framework_MockObject_MockObject $cookieMetadataMock */
        $cookieMetadataMock = $this->getMockBuilder(PublicCookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($cookieMetadataMock);

        $this->cookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                MessagePlugin::MESSAGES_COOKIES_NAME,
                json_encode($expectedMessages),
                $cookieMetadataMock
            );
        $this->cookieManagerMock->expects($this->once())
            ->method('getCookie')
            ->with(
                MessagePlugin::MESSAGES_COOKIES_NAME
            )
            ->willReturn(json_encode([]));

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturnCallback(
                function ($data) {
                    return json_decode($data, true);
                }
            );
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturnCallback(
                function ($data) {
                    return json_encode($data);
                }
            );

        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $messageMock */
        $messageMock = $this->getMockBuilder(MessageInterface::class)
            ->getMock();
        $messageMock->expects($this->once())
            ->method('getType')
            ->willReturn($messageType);

        $this->interpretationStrategyMock->expects($this->once())
            ->method('interpret')
            ->with($messageMock)
            ->willReturn($messageText);

        $this->inlineTranslateMock->expects($this->once())
            ->method('isAllowed')
            ->willReturn(true);

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$messageMock]);

        $this->managerMock->expects($this->once())
            ->method('getMessages')
            ->with(true, null)
            ->willReturn($collectionMock);

        $this->assertEquals($resultMock, $this->model->afterRenderResult($resultMock, $resultMock));
    }
}
