<?php
/**
 * Test for \Magento\Integration\Model\OauthService
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Model;

use Magento\Integration\Model\Oauth\Token;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OauthServiceTest extends \PHPUnit\Framework\TestCase
{
    const VALUE_CONSUMER_ID = 1;

    const VALUE_CONSUMER_KEY = 'asdfghjklaqwerfdtyuiomnbgfdhbsoi';

    const VALUE_TOKEN_TYPE = 'access';

    /** @var \Magento\Integration\Model\Oauth\ConsumerFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_consumerFactory;

    /** @var \Magento\Integration\Model\Oauth\Token\Provider|\PHPUnit_Framework_MockObject_MockObject */
    protected $_tokenProviderMock;

    /** @var \Magento\Integration\Model\Oauth\Consumer|\PHPUnit_Framework_MockObject_MockObject */
    private $_consumerMock;

    /** @var \Magento\Integration\Model\Integration|\PHPUnit_Framework_MockObject_MockObject */
    private $_emptyConsumerMock;

    /**
     * @var \Magento\Integration\Model\Oauth\Token|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_tokenMock;

    /** @var \Magento\Integration\Model\OauthService */
    private $_service;

    /** @var array */
    private $_consumerData;

    /**
     * @var \Magento\Integration\Model\Oauth\TokenFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_tokenFactoryMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->_consumerFactory = $this->getMockBuilder(\Magento\Integration\Model\Oauth\ConsumerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->_tokenProviderMock = $this->getMockBuilder(
            \Magento\Integration\Model\Oauth\Token\Provider::class
        )->disableOriginalConstructor()->getMock();
        $this->_tokenMock = $this->getMockBuilder(
            \Magento\Integration\Model\Oauth\Token::class
        )->disableOriginalConstructor()->setMethods(
            ['createVerifierToken', 'getType', '__wakeup', 'delete']
        )->getMock();

        $this->_tokenFactoryMock = $this->createPartialMock(
            \Magento\Integration\Model\Oauth\TokenFactory::class,
            ['create']
        );
        $this->_tokenFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_tokenMock));
        $this->_consumerMock = $this->getMockBuilder(
            \Magento\Integration\Model\Oauth\Consumer::class
        )->disableOriginalConstructor()->setMethods(
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
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_service = new \Magento\Integration\Model\OauthService(
            $this->createMock(\Magento\Store\Model\StoreManagerInterface::class),
            $this->_consumerFactory,
            $this->_tokenFactoryMock,
            $this->createMock(\Magento\Integration\Helper\Oauth\Data::class),
            $this->createMock(\Magento\Framework\HTTP\ZendClient::class),
            $this->createMock(\Psr\Log\LoggerInterface::class),
            $this->createMock(\Magento\Framework\Oauth\Helper\Oauth::class),
            $this->_tokenProviderMock
        );
        $this->_emptyConsumerMock = $this->getMockBuilder(
            \Magento\Integration\Model\Integration::class
        )->disableOriginalConstructor()->setMethods(
            ['getData', 'load', 'getId', 'save', 'delete', '__wakeup']
        )->getMock();
        $this->_emptyConsumerMock->expects($this->any())->method('getId')->will($this->returnValue(null));
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
        )->will(
            $this->returnValue(self::VALUE_CONSUMER_ID)
        );
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            self::VALUE_CONSUMER_ID
        )->will(
            $this->returnValue($this->_consumerMock)
        );
        $this->_consumerMock->expects($this->once())->method('delete')->will($this->returnValue($this->_consumerMock));
        $this->_consumerMock->expects($this->any())->method('getData')->will($this->returnValue($this->_consumerData));
        $consumerData = $this->_service->deleteConsumer(self::VALUE_CONSUMER_ID);
        $this->assertEquals($this->_consumerData['entity_id'], $consumerData['entity_id']);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\IntegrationException
     * @expectedExceptionMessage A consumer with ID "1" doesn't exist. Verify the ID and try again.
     */
    public function testDeleteException()
    {
        $this->_consumerMock->expects($this->any())->method('getId')->will($this->returnValue(null));
        $this->_consumerMock->expects($this->once())->method('load')->will($this->returnSelf());
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
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->returnValue($this->_tokenMock)
        );

        $this->_tokenProviderMock->expects($this->any())->method('createRequestToken')->with($this->_consumerMock);

        $this->_tokenProviderMock->expects($this->any())->method('getAccessToken')->with($this->_consumerMock);

        $this->_tokenFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_tokenMock));

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
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->returnValue($this->_tokenMock)
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
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->throwException(
                new \Magento\Framework\Oauth\Exception(
                    __('A token with consumer ID 0 does not exist')
                )
            )
        );

        $this->_tokenMock->expects($this->never())->method('delete');

        $this->_tokenFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_tokenMock)
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
        )->will(
            $this->returnValue($this->_consumerMock)
        );
        $this->_consumerMock->expects($this->any())->method('getData')->will($this->returnValue($this->_consumerData));
        $consumer = $this->_service->loadConsumer(self::VALUE_CONSUMER_ID);
        $consumerData = $consumer->getData();
        $this->assertEquals($this->_consumerData['entity_id'], $consumerData['entity_id']);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testLoadConsumerException()
    {
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->will(
            $this->throwException(
                new \Magento\Framework\Oauth\Exception(
                    __(
                        "The oAuth consumer account couldn't be loaded due to an unexpected error. "
                        . "Please try again later."
                    )
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
        )->will(
            $this->returnValue($this->_consumerMock)
        );
        $this->_consumerMock->expects($this->any())->method('getData')->will($this->returnValue($this->_consumerData));
        $consumer = $this->_service->loadConsumerByKey(self::VALUE_CONSUMER_KEY);
        $consumerData = $consumer->getData();
        $this->assertEquals($this->_consumerData['key'], $consumerData['key']);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Oauth\Exception
     */
    public function testLoadConsumerByKeyException()
    {
        $this->_consumerMock->expects(
            $this->once()
        )->method(
            'load'
        )->will(
            $this->throwException(
                new \Magento\Framework\Oauth\Exception(
                    __(
                        "The oAuth consumer account couldn't be loaded due to an unexpected error. "
                        . "Please try again later."
                    )
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
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->returnValue($this->_tokenMock)
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
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->returnValue($this->_tokenMock)
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
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->returnValue($this->_tokenMock)
        );

        $this->assertFalse($this->_service->getAccessToken(self::VALUE_CONSUMER_ID), false);
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
        )->will(
            $this->returnValue($this->_consumerMock)
        );

        $this->_tokenMock->expects($this->once())->method('getType')->will($this->returnValue(Token::TYPE_ACCESS));

        $this->_tokenProviderMock->expects(
            $this->any()
        )->method(
            'getIntegrationTokenByConsumerId'
        )->will(
            $this->returnValue($this->_tokenMock)
        );

        $this->assertEquals($this->_service->getAccessToken(self::VALUE_CONSUMER_ID), $this->_tokenMock);
    }
}
