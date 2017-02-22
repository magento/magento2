<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Helper\Oauth;

/**
 * Test for \Magento\Integration\Model\Oauth\Consumer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManagerMock;

    /** @var \Magento\Integration\Model\Oauth\ConsumerFactory */
    protected $_consumerFactory;

    /** @var \Magento\Integration\Model\Oauth\Consumer */
    protected $_consumerMock;

    /** @var \Magento\Framework\HTTP\ZendClient */
    protected $_httpClientMock;

    /** @var \Magento\Integration\Model\Oauth\TokenFactory */
    protected $_tokenFactory;

    /** @var \Magento\Integration\Model\Oauth\Token */
    protected $_tokenMock;

    /** @var \Magento\Store\Model\Store */
    protected $_storeMock;

    /** @var \Magento\Integration\Helper\Oauth\Data */
    protected $_dataHelper;

    /** @var \Magento\Integration\Api\OauthServiceInterface */
    protected $_oauthService;

    /** @var \Psr\Log\LoggerInterface */
    protected $_loggerMock;

    protected function setUp()
    {
        $this->_consumerFactory = $this->getMockBuilder('Magento\Integration\Model\Oauth\ConsumerFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->_consumerMock = $this->getMockBuilder(
            'Magento\Integration\Model\Oauth\Consumer'
        )->disableOriginalConstructor()->getMock();
        $this->_consumerFactory->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenFactory = $this->getMockBuilder(
            'Magento\Integration\Model\Oauth\TokenFactory'
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->_tokenMock = $this->getMockBuilder(
            'Magento\Integration\Model\Oauth\Token'
        )->disableOriginalConstructor()->getMock();
        $this->_tokenFactory->expects($this->any())->method('create')->will($this->returnValue($this->_tokenMock));

        $this->_storeManagerMock = $this->getMockBuilder(
            'Magento\Store\Model\StoreManagerInterface'
        )->disableOriginalConstructor()->getMockForAbstractClass();
        $this->_storeMock = $this->getMockBuilder(
            'Magento\Store\Model\Store'
        )->disableOriginalConstructor()->getMock();
        $this->_storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->_storeMock)
        );

        $this->_dataHelper = $this->getMockBuilder(
            'Magento\Integration\Helper\Oauth\Data'
        )->disableOriginalConstructor()->getMock();

        $oauthHelperMock = $this->getMockBuilder(
            'Magento\Framework\Oauth\Helper\Oauth'
        )->disableOriginalConstructor()->getMock();

        $tokenProviderMock = $this->getMockBuilder(
            'Magento\Integration\Model\Oauth\Token\Provider'
        )->disableOriginalConstructor()->getMock();

        $this->_httpClientMock = $this->getMockBuilder(
            'Magento\Framework\HTTP\ZendClient'
        )->disableOriginalConstructor()->getMock();
        $this->_loggerMock = $this->getMockBuilder(
            'Psr\Log\LoggerInterface'
        )->getMock();

        $this->_oauthService = new \Magento\Integration\Model\OauthService(
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

    protected function tearDown()
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
        $key = $this->_generateRandomString(\Magento\Framework\Oauth\Helper\Oauth::LENGTH_CONSUMER_KEY);
        $secret = $this->_generateRandomString(\Magento\Framework\Oauth\Helper\Oauth::LENGTH_CONSUMER_SECRET);

        $consumerData = ['name' => 'Integration Name', 'key' => $key, 'secret' => $secret];
        $this->_consumerMock->expects($this->once())->method('setData')->will($this->returnSelf());
        $this->_consumerMock->expects($this->once())->method('save')->will($this->returnSelf());

        /** @var \Magento\Integration\Model\Oauth\Consumer $consumer */
        $consumer = $this->_oauthService->createConsumer($consumerData);

        $this->assertEquals($consumer, $this->_consumerMock, 'Consumer object was expected to be returned');
    }

    public function testPostToConsumer()
    {
        $consumerId = 1;

        $key = $this->_generateRandomString(\Magento\Framework\Oauth\Helper\Oauth::LENGTH_CONSUMER_KEY);
        $secret = $this->_generateRandomString(\Magento\Framework\Oauth\Helper\Oauth::LENGTH_CONSUMER_SECRET);
        $oauthVerifier = $this->_generateRandomString(\Magento\Framework\Oauth\Helper\Oauth::LENGTH_TOKEN_VERIFIER);

        $consumerData = ['entity_id' => $consumerId, 'key' => $key, 'secret' => $secret];

        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $this->equalTo($consumerId)
        )->will(
            $this->returnSelf()
        );

        $dateHelperMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $dateHelperMock->expects($this->any())->method('gmtDate');

        $dateHelper = new \ReflectionProperty('Magento\Integration\Model\OauthService', '_dateHelper');
        $dateHelper->setAccessible(true);
        $dateHelper->setValue($this->_oauthService, $dateHelperMock);

        $this->_consumerMock->expects($this->once())->method('getId')->will($this->returnValue($consumerId));
        $this->_consumerMock->expects($this->once())->method('getData')->will($this->returnValue($consumerData));
        $this->_httpClientMock->expects(
            $this->once()
        )->method(
            'setUri'
        )->with(
            'http://www.magento.com'
        )->will(
            $this->returnSelf()
        );
        $this->_httpClientMock->expects($this->once())->method('setParameterPost')->will($this->returnSelf());
        $this->_tokenMock->expects(
            $this->once()
        )->method(
            'createVerifierToken'
        )->with(
            $consumerId
        )->will(
            $this->returnSelf()
        );
        $this->_tokenMock->expects($this->any())->method('getVerifier')->will($this->returnValue($oauthVerifier));
        $this->_dataHelper->expects($this->once())->method('getConsumerPostMaxRedirects')->will($this->returnValue(5));
        $this->_dataHelper->expects($this->once())->method('getConsumerPostTimeout')->will($this->returnValue(120));

        $verifier = $this->_oauthService->postToConsumer($consumerId, 'http://www.magento.com');

        $this->assertEquals($oauthVerifier, $verifier, 'Checking Oauth Verifier');
    }

    private function _generateRandomString($length)
    {
        return substr(
            str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)),
            0,
            $length
        );
    }
}
