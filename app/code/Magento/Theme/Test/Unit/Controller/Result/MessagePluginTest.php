<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Result;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Theme\Controller\Result\MessagePlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MessagePluginTest extends TestCase
{
    /** @var MessagePlugin */
    private $model;

    /** @var CookieManagerInterface|MockObject */
    private $cookieManagerMock;

    /** @var CookieMetadataFactory|MockObject */
    private $cookieMetadataFactoryMock;

    /** @var ManagerInterface|MockObject */
    private $managerMock;

    /** @var InterpretationStrategyInterface|MockObject */
    private $interpretationStrategyMock;

    /** @var JsonSerializer|MockObject */
    private $serializerMock;

    /** @var InlineInterface|MockObject */
    private $inlineTranslateMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private $sessionConfigMock;

    protected function setUp(): void
    {
        $this->cookieManagerMock = $this->createMock(CookieManagerInterface::class);
        $this->cookieMetadataFactoryMock = $this->createMock(CookieMetadataFactory::class);
        $this->managerMock = $this->createMock(ManagerInterface::class);
        $this->interpretationStrategyMock = $this->createMock(InterpretationStrategyInterface::class);
        $this->serializerMock = $this->createMock(JsonSerializer::class);
        $this->inlineTranslateMock = $this->createMock(InlineInterface::class);
        $this->sessionConfigMock = $this->createMock(ConfigInterface::class);

        $this->model = new MessagePlugin(
            $this->cookieManagerMock,
            $this->cookieMetadataFactoryMock,
            $this->managerMock,
            $this->interpretationStrategyMock,
            $this->serializerMock,
            $this->inlineTranslateMock,
            $this->sessionConfigMock
        );
    }

    public function testAfterRenderResultJson()
    {
        /** @var Json|MockObject $resultMock */
        $resultMock = $this->createMock(Json::class);

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

        /** @var Redirect|MockObject $resultMock */
        $resultMock = $this->createMock(Redirect::class);
        /** @var PublicCookieMetadata|MockObject $cookieMetadataMock */
        $cookieMetadataMock = $this->createMock(PublicCookieMetadata::class);
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

        /** @var MessageInterface|MockObject $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $messageMock->expects($this->once())
            ->method('getType')
            ->willReturn($messageType);
        $this->interpretationStrategyMock->expects($this->once())
            ->method('interpret')
            ->with($messageMock)
            ->willReturn($messageText);

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->createMock(Collection::class);
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
        /** @var Redirect|MockObject $resultMock */
        $resultMock = $this->createMock(Redirect::class);

        $this->cookieManagerMock->expects($this->never())
            ->method('getCookie');
        $this->serializerMock->expects($this->never())
            ->method('unserialize');
        $this->serializerMock->expects($this->never())
            ->method('serialize');

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->managerMock->expects($this->once())
            ->method('getMessages')
            ->with(true, null)
            ->willReturn($collectionMock);

        $this->cookieMetadataFactoryMock->expects($this->never())
            ->method('createPublicCookieMetadata');

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

        /** @var Redirect|MockObject $resultMock */
        $resultMock = $this->createMock(Redirect::class);
        /** @var PublicCookieMetadata|MockObject $cookieMetadataMock */
        $cookieMetadataMock = $this->createMock(PublicCookieMetadata::class);
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

        /** @var MessageInterface|MockObject $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $messageMock->expects($this->once())
            ->method('getType')
            ->willReturn($messageType);
        $this->interpretationStrategyMock->expects($this->once())
            ->method('interpret')
            ->with($messageMock)
            ->willReturn($messageText);

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->createMock(Collection::class);
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

        /** @var Redirect|MockObject $resultMock */
        $resultMock = $this->createMock(Redirect::class);
        /** @var PublicCookieMetadata|MockObject $cookieMetadataMock */
        $cookieMetadataMock = $this->createMock(PublicCookieMetadata::class);
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

        /** @var MessageInterface|MockObject $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $messageMock->expects($this->once())
            ->method('getType')
            ->willReturn($messageType);
        $this->interpretationStrategyMock->expects($this->once())
            ->method('interpret')
            ->with($messageMock)
            ->willReturn($messageText);

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->createMock(Collection::class);
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

        /** @var Redirect|MockObject $resultMock */
        $resultMock = $this->createMock(Redirect::class);
        /** @var PublicCookieMetadata|MockObject $cookieMetadataMock */
        $cookieMetadataMock = $this->createMock(PublicCookieMetadata::class);
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

        /** @var MessageInterface|MockObject $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $messageMock->expects($this->once())
            ->method('getType')
            ->willReturn($messageType);
        $this->interpretationStrategyMock->expects($this->once())
            ->method('interpret')
            ->with($messageMock)
            ->willReturn($messageText);

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->createMock(Collection::class);
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
    public function testAfterRenderResultWithAllowedInlineTranslate(): void
    {
        $messageType = 'message1type';
        $messageText = '{{{message1text}}{{message1text}}{{message1text}}{{theme/luma}}}';
        $expectedMessages = [
            [
                'type' => $messageType,
                'text' => 'message1text',
            ],
        ];

        /** @var Redirect|MockObject $resultMock */
        $resultMock = $this->createMock(Redirect::class);
        /** @var PublicCookieMetadata|MockObject $cookieMetadataMock */
        $cookieMetadataMock = $this->createMock(PublicCookieMetadata::class);
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

        /** @var MessageInterface|MockObject $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
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

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$messageMock]);
        $this->managerMock->expects($this->once())
            ->method('getMessages')
            ->with(true, null)
            ->willReturn($collectionMock);

        $this->assertEquals($resultMock, $this->model->afterRenderResult($resultMock, $resultMock));
    }

    public function testSetCookieWithCookiePath()
    {
        /** @var Redirect|MockObject $resultMock */
        $resultMock = $this->createMock(Redirect::class);
        /** @var PublicCookieMetadata|MockObject $cookieMetadataMock */
        $cookieMetadataMock = $this->createMock(PublicCookieMetadata::class);
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('createPublicCookieMetadata')
            ->willReturn($cookieMetadataMock);

        /** @var MessageInterface|MockObject $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$messageMock]);

        $this->managerMock->expects($this->once())
            ->method('getMessages')
            ->with(true, null)
            ->willReturn($collectionMock);

        /* Test that getCookiePath is called during cookie setup */
        $this->sessionConfigMock->expects($this->once())
            ->method('getCookiePath')
            ->willReturn('/pub');
        $cookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with('/pub')
            ->willReturn($cookieMetadataMock);

        $this->model->afterRenderResult($resultMock, $resultMock);
    }
}
