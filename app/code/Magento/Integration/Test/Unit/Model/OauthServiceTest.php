<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Model;

use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\Oauth\Exception;
use Magento\Framework\Oauth\Helper\Oauth;
use Magento\Integration\Helper\Oauth\Data;
use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Oauth\Consumer;
use Magento\Integration\Model\Oauth\ConsumerFactory;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\Token\Provider;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Model\OauthService;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OauthServiceTest extends TestCase
{
    public const VALUE_CONSUMER_ID = 1;

    public const VALUE_CONSUMER_KEY = 'asdfghjklaqwerfdtyuiomnbgfdhbsoi';

    public const VALUE_TOKEN_TYPE = 'access';

    /** @var ConsumerFactory|MockObject */
    protected $_consumerFactory;

    /** @var Provider|MockObject */
    protected $_tokenProviderMock;

    /** @var Consumer|MockObject */
    private $_consumerMock;

    /** @var Integration|MockObject */
    private $_emptyConsumerMock;

    /**
     * @var Token|MockObject
     */
    private $_tokenMock;

    /** @var OauthService */
    private $_service;

    /** @var array */
    private $_consumerData;

    /**
     * @var TokenFactory|MockObject
     */
    private $_tokenFactoryMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->_consumerFactory = $this->getMockBuilder(ConsumerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->_tokenProviderMock = $this->getMockBuilder(
            Provider::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_tokenMock = $this->getMockBuilder(
            Token::class
        )->disableOriginalConstructor()
            ->onlyMethods(['createVerifierToken', '__wakeup', 'delete'])
            ->addMethods(
                ['getType']
            )->getMock();

        $this->_tokenFactoryMock = $this->createPartialMock(
            TokenFactory::class,
            ['create']
        );
        $this->_tokenFactoryMock->expects($this->any())->method('create')->willReturn($this->_tokenMock);
        $this->_consumerMock = $this->getMockBuilder(
            Consumer::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['getData', 'getId', 'load', 'save', 'delete', '__wakeup']
            )->getMock();
        $this->_consumerData = [
            'entity_id' => self::VALUE_CONSUMER_ID,
            'key' => self::VALUE_CONSUMER_KEY,
            'secret' => 'iuyytrfdsdfbnnhbmkkjlkjl',
            'created_at' => '',
            'updated_at' => '',
            'callback_url' => '',
            'rejected_callback_url' => ''
        ];
        $this->_consumerFactory->expects(
            $this->any()
        )->method(
            'create'
        )->willReturn(
            $this->_consumerMock
        );

        $this->_service = new OauthService(
            $this->getMockForAbstractClass(StoreManagerInterface::class),
            $this->_consumerFactory,
            $this->_tokenFactoryMock,
            $this->createMock(Data::class),
            $this->createMock(LaminasClient::class),
            $this->getMockForAbstractClass(LoggerInterface::class),
            $this->createMock(Oauth::class),
            $this->_tokenProviderMock
        );
        $this->_emptyConsumerMock = $this->getMockBuilder(
            Integration::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['getData', 'load', 'getId', 'save', 'delete', '__wakeup']
            )->getMock();
        $this->_emptyConsumerMock->expects($this->any())->method('getId')->willReturn(null);
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'getId'
        )->willReturn(
            self::VALUE_CONSUMER_ID
        );
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->willReturn(
            $this->_consumerMock
        );
        $this->_consumerMock->expects($this->once())->method('delete')->willReturn($this->_consumerMock);
        $this->_consumerMock->expects($this->any())->method('getData')->willReturn($this->_consumerData);
        $consumerData = $this->_service->deleteConsumer(self::VALUE_CONSUMER_ID);
        $this->assertEquals($this->_consumerData['entity_id'], $consumerData['entity_id']);
    }

    /**
     * @return void
     */
    public function testDeleteException()
    {
        $this->expectException('Magento\Framework\Exception\IntegrationException');
        $this->expectExceptionMessage('A consumer with ID "1" doesn\'t exist. Verify the ID and try again.');
        $this->_consumerMock->expects($this->any())->method('getId')->willReturn(null);
        $this->_consumerMock->expects($this->once())->method('load')->willReturnSelf();
        $this->_consumerMock->expects($this->never())->method('delete');
        $this->_service->deleteConsumer(self::VALUE_CONSUMER_ID);
    }

    /**
     * @return void
     */
    public function testCreateAccessTokenAndClearExisting()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->willReturn(
            $this->_consumerMock
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->willReturn(
            $this->_tokenMock
        );

        $this->_tokenProviderMock->expects($this->any())->method('createRequestToken')->with($this->_consumerMock);

        $this->_tokenProviderMock->expects($this->any())->method('getAccessToken')->with($this->_consumerMock);

        $this->_tokenFactoryMock->expects($this->any())->method('create')->willReturn($this->_tokenMock);

        $this->_tokenMock->expects($this->once())->method('delete');

        $this->_tokenMock->expects($this->once())->method('createVerifierToken')->with(self::VALUE_CONSUMER_ID);

        $this->_tokenProviderMock->expects($this->once())->method('createRequestToken')->with($this->_consumerMock);

        $this->_tokenProviderMock->expects($this->once())->method('getAccessToken')->with($this->_consumerMock);

        $this->assertTrue($this->_service->createAccessToken(self::VALUE_CONSUMER_ID, true));
    }

    /**
     * @return void
     */
    public function testCreateAccessTokenWithoutClearingExisting()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->willReturn(
            $this->_consumerMock
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->willReturn(
            $this->_tokenMock
        );

        $this->_tokenMock->expects($this->never())->method('delete');

        $this->assertFalse($this->_service->createAccessToken(self::VALUE_CONSUMER_ID, false));
    }

    /**
     * @return void
     */
    public function testCreateAccessTokenInvalidConsumerId()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            0
        )->willReturn(
            $this->_consumerMock
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->willThrowException(
            new Exception(
                __('A token with consumer ID 0 does not exist')
            )
        );

        $this->_tokenMock->expects($this->never())->method('delete');

        $this->_tokenFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $this->_tokenMock
        );

        $this->_tokenMock->expects($this->once())->method('createVerifierToken');

        $this->_tokenProviderMock->expects($this->once())->method('createRequestToken');

        $this->_tokenProviderMock->expects($this->once())->method('getAccessToken');

        $this->assertTrue($this->_service->createAccessToken(0, false));
    }

    /**
     * @return void
     */
    public function testLoadConsumer()
    {
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->willReturn(
            $this->_consumerMock
        );
        $this->_consumerMock->expects($this->any())->method('getData')->willReturn($this->_consumerData);
        $consumer = $this->_service->loadConsumer(self::VALUE_CONSUMER_ID);
        $consumerData = $consumer->getData();
        $this->assertEquals($this->_consumerData['entity_id'], $consumerData['entity_id']);
    }

    /**
     * @return void
     */
    public function testLoadConsumerException()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->willThrowException(
            new Exception(
                __(
                    "The oAuth consumer account couldn't be loaded due to an unexpected error. "
                    . "Please try again later."
                )
            )
        );
        $this->_service->loadConsumer(self::VALUE_CONSUMER_ID);

        $this->expectExceptionMessage(
            "The oAuth consumer account couldn't be loaded due to an unexpected error. Please try again later."
        );
    }

    /**
     * @return void
     */
    public function testLoadConsumerByKey()
    {
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_KEY,
            'key'
        )->willReturn(
            $this->_consumerMock
        );
        $this->_consumerMock->expects($this->any())->method('getData')->willReturn($this->_consumerData);
        $consumer = $this->_service->loadConsumerByKey(self::VALUE_CONSUMER_KEY);
        $consumerData = $consumer->getData();
        $this->assertEquals($this->_consumerData['key'], $consumerData['key']);
    }

    /**
     * @return void
     */
    public function testLoadConsumerByKeyException()
    {
        $this->expectException('Magento\Framework\Oauth\Exception');
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->willThrowException(
            new Exception(
                __(
                    "The oAuth consumer account couldn't be loaded due to an unexpected error. "
                    . "Please try again later."
                )
            )
        );
        $this->_service->loadConsumerByKey(self::VALUE_CONSUMER_KEY);

        $this->expectExceptionMessage(
            "The oAuth consumer account couldn't be loaded due to an unexpected error. Please try again later."
        );
    }

    /**
     * @return void
     */
    public function testDeleteToken()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->willReturn(
            $this->_consumerMock
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->willReturn(
            $this->_tokenMock
        );

        $this->_tokenMock->expects($this->once())->method('delete');

        $this->assertTrue($this->_service->deleteIntegrationToken(self::VALUE_CONSUMER_ID));
    }

    /**
     * @return void
     */
    public function testDeleteTokenNegative()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->willReturn(
            $this->_consumerMock
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->willReturn(
            $this->_tokenMock
        );

        $this->_tokenMock->expects($this->never())->method('delete');

        $this->assertFalse($this->_service->deleteIntegrationToken(null));
    }

    /**
     * @return void
     */
    public function testGetAccessTokenNoAccess()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->willReturn(
            $this->_consumerMock
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->willReturn(
            $this->_tokenMock
        );

        $this->assertFalse($this->_service->getAccessToken(self::VALUE_CONSUMER_ID));
    }

    /**
     * @return void
     */
    public function testGetAccessSuccess()
    {
        $this->_consumerMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->willReturn(
            $this->_consumerMock
        );

        $this->_tokenMock->expects($this->once())->method('getType')->willReturn(Token::TYPE_ACCESS);

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->willReturn(
            $this->_tokenMock
        );

        $this->assertEquals($this->_service->getAccessToken(self::VALUE_CONSUMER_ID), $this->_tokenMock);
    }
}
