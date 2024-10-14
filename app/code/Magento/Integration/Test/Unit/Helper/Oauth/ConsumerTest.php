<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Helper\Oauth;

use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\Oauth\Helper\Oauth;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Helper\Oauth\Data;
use Magento\Integration\Model\Oauth\Consumer;
use Magento\Integration\Model\Oauth\ConsumerFactory;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\Token\Provider;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Model\OauthService;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for \Magento\Integration\Model\Oauth\Consumer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumerTest extends TestCase
{
    /** @var StoreManagerInterface */
    protected $_storeManagerMock;

    /** @var ConsumerFactory */
    protected $_consumerFactory;

    /** @var Consumer */
    protected $_consumerMock;

    /** @var LaminasClient */
    protected $_httpClientMock;

    /** @var TokenFactory */
    protected $_tokenFactory;

    /** @var Token */
    protected $_tokenMock;

    /** @var Store */
    protected $_storeMock;

    /** @var Data */
    protected $_dataHelper;

    /** @var OauthServiceInterface */
    protected $_oauthService;

    /** @var LoggerInterface */
    protected $_loggerMock;

    protected function setUp(): void
    {
        $this->_consumerFactory = $this->getMockBuilder(ConsumerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->_consumerMock = $this->getMockBuilder(
            Consumer::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_consumerFactory->expects(
            $this->any()
        )->method(
            'create'
        )->willReturn(
            $this->_consumerMock
        );

        $this->_tokenFactory = $this->getMockBuilder(
            TokenFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])->getMock();
        $this->_tokenMock = $this->getMockBuilder(
            Token::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_tokenFactory->expects($this->any())->method('create')->willReturn($this->_tokenMock);

        $this->_storeManagerMock = $this->getMockBuilder(
            StoreManagerInterface::class
        )->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_storeMock = $this->getMockBuilder(
            Store::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->willReturn(
            $this->_storeMock
        );

        $this->_dataHelper = $this->getMockBuilder(
            Data::class
        )->disableOriginalConstructor()
            ->getMock();

        $oauthHelperMock = $this->getMockBuilder(
            Oauth::class
        )->disableOriginalConstructor()
            ->getMock();

        $tokenProviderMock = $this->getMockBuilder(
            Provider::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->_httpClientMock = $this->getMockBuilder(
            LaminasClient::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_loggerMock = $this->getMockBuilder(
            LoggerInterface::class
        )->getMock();

        $this->_oauthService = new OauthService(
            $this->_storeManagerMock,
            $this->_consumerFactory,
            $this->_tokenFactory,
            $this->_dataHelper,
            $this->_httpClientMock,
            $this->_loggerMock,
            $oauthHelperMock,
            $tokenProviderMock
        );
    }

    protected function tearDown(): void
    {
        unset($this->_storeManagerMock);
        unset($this->_consumerFactory);
        unset($this->_tokenFactory);
        unset($this->_dataHelper);
        unset($this->_httpClientMock);
        unset($this->_loggerMock);
        unset($this->_oauthService);
    }

    public function testCreateConsumer()
    {
        $key = $this->_generateRandomString(Oauth::LENGTH_CONSUMER_KEY);
        $secret = $this->_generateRandomString(Oauth::LENGTH_CONSUMER_SECRET);

        $consumerData = ['name' => 'Integration Name', 'key' => $key, 'secret' => $secret];
        $this->_consumerMock->expects($this->once())->method('setData')->willReturnSelf();
        $this->_consumerMock->expects($this->once())->method('save')->willReturnSelf();

        /** @var Consumer $consumer */
        $consumer = $this->_oauthService->createConsumer($consumerData);

        $this->assertEquals($consumer, $this->_consumerMock, 'Consumer object was expected to be returned');
    }

    public function testPostToConsumer()
    {
        $consumerId = 1;

        $key = $this->_generateRandomString(Oauth::LENGTH_CONSUMER_KEY);
        $secret = $this->_generateRandomString(Oauth::LENGTH_CONSUMER_SECRET);
        $oauthVerifier = $this->_generateRandomString(Oauth::LENGTH_TOKEN_VERIFIER);

        $consumerData = ['entity_id' => $consumerId, 'key' => $key, 'secret' => $secret];

        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $this->equalTo($consumerId)
        )->willReturnSelf(
        );

        $dateHelperMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateHelperMock->expects($this->any())->method('gmtDate');

        $dateHelper = new \ReflectionProperty(OauthService::class, '_dateHelper');
        $dateHelper->setAccessible(true);
        $dateHelper->setValue($this->_oauthService, $dateHelperMock);

        $this->_consumerMock->expects($this->once())->method('getId')->willReturn($consumerId);
        $this->_consumerMock->expects($this->once())->method('getData')->willReturn($consumerData);
        $this->_httpClientMock->expects(
            $this->once()
        )->method(
            'setUri'
        )->with(
            'http://www.magento.com'
        )->willReturnSelf(
        );
        $this->_httpClientMock->expects($this->once())->method('setParameterPost')->willReturnSelf();
        $this->_tokenMock->expects(
            $this->once()
        )->method(
            'createVerifierToken'
        )->with(
            $consumerId
        )->willReturnSelf(
        );
        $this->_tokenMock->expects($this->any())->method('getVerifier')->willReturn($oauthVerifier);
        $this->_dataHelper->expects($this->once())->method('getConsumerPostMaxRedirects')->willReturn(5);
        $this->_dataHelper->expects($this->once())->method('getConsumerPostTimeout')->willReturn(120);

        $verifier = $this->_oauthService->postToConsumer($consumerId, 'http://www.magento.com');

        $this->assertEquals($oauthVerifier, $verifier, 'Checking Oauth Verifier');
    }

    /**
     * @param $length
     * @return bool|string
     */
    private function _generateRandomString($length)
    {
        return substr(
            str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)),
            0,
            $length
        );
    }
}
