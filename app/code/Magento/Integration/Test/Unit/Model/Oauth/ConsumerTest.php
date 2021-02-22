<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\Oauth;

use Magento\Framework\Url\Validator as UrlValidator;
use Zend\Validator\Uri as ZendUriValidator;
use Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength;

/**
 * Test for \Magento\Integration\Model\Oauth\Consumer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Integration\Model\Oauth\Consumer
     */
    protected $consumerModel;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registryMock;

    /**
     * @var KeyLength
     */
    protected $keyLengthValidator;

    /**
     * @var \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLengthFactory
     */
    protected $keyLengthValidatorFactory;

    /**
     * @var UrlValidator
     */
    protected $urlValidator;

    /**
     * @var \Magento\Integration\Helper\Oauth\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $oauthDataMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceCollectionMock;

    /**
     * @var array
     */
    protected $validDataArray;

    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(\Magento\Framework\Model\Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($eventManagerMock);

        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);

        $this->keyLengthValidator = new KeyLength();

        $this->urlValidator = new UrlValidator(new ZendUriValidator());

        $this->oauthDataMock = $this->createPartialMock(
            \Magento\Integration\Helper\Oauth\Data::class,
            ['getConsumerExpirationPeriod']
        );
        $this->oauthDataMock->expects($this->any())
            ->method('getConsumerExpirationPeriod')
            ->willReturn(\Magento\Integration\Helper\Oauth\Data::CONSUMER_EXPIRATION_PERIOD_DEFAULT);

        $this->resourceMock = $this->createPartialMock(
            \Magento\Integration\Model\ResourceModel\Oauth\Consumer::class,
            ['getIdFieldName', 'selectByCompositeKey', 'deleteOldEntries']
        );
        $this->resourceCollectionMock = $this->createMock(\Magento\Framework\Data\Collection\AbstractDb::class);
        $this->consumerModel = new \Magento\Integration\Model\Oauth\Consumer(
            $this->contextMock,
            $this->registryMock,
            $this->keyLengthValidator,
            $this->urlValidator,
            $this->oauthDataMock,
            $this->resourceMock,
            $this->resourceCollectionMock
        );

        $this->validDataArray = [
            'key' => md5(uniqid()),
            'secret' => md5(uniqid()),
            'callback_url' => 'http://example.com/callback',
            'rejected_callback_url' => 'http://example.com/rejectedCallback'
        ];
    }

    public function testBeforeSave()
    {
        try {
            $this->consumerModel->setData($this->validDataArray);
            $this->consumerModel->beforeSave();
        } catch (\Exception $e) {
            $this->fail('Exception not expected for beforeSave with valid data.');
        }
    }

    public function testValidate()
    {
        $this->consumerModel->setData($this->validDataArray);
        $this->assertTrue($this->consumerModel->validate());
    }

    /**
     */
    public function testValidateInvalidData()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Invalid Callback URL');

        $this->validDataArray['callback_url'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    /**
     */
    public function testValidateInvalidCallback()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Invalid Callback URL');

        $this->validDataArray['callback_url'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    /**
     */
    public function testValidateInvalidRejectedCallback()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Invalid Rejected Callback URL');

        $this->validDataArray['rejected_callback_url'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    /**
     */
    public function testValidateInvalidConsumerKey()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Consumer Key \'invalid\' is less than 32 characters long');

        $this->validDataArray['key'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    /**
     */
    public function testValidateInvalidConsumerSecret()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Consumer Secret \'invalid\' is less than 32 characters long');

        $this->validDataArray['secret'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    public function testGetConsumerExpirationPeriodValid()
    {
        $dateHelperMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateHelperMock->expects($this->at(0))->method('gmtTimestamp')->willReturn(time());
        $dateHelperMock->expects($this->at(1))->method('gmtTimestamp')->willReturn(time() - 100);

        $dateHelper = new \ReflectionProperty(\Magento\Integration\Model\Oauth\Consumer::class, '_dateHelper');
        $dateHelper->setAccessible(true);
        $dateHelper->setValue($this->consumerModel, $dateHelperMock);

        $this->consumerModel->setUpdatedAt(time());
        $this->assertTrue($this->consumerModel->isValidForTokenExchange());
    }

    public function testGetConsumerExpirationPeriodExpired()
    {
        $dateHelperMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateHelperMock->expects($this->at(0))->method('gmtTimestamp')->willReturn(time());
        $dateHelperMock->expects($this->at(1))->method('gmtTimestamp')->willReturn(time() - 1000);

        $dateHelper = new \ReflectionProperty(\Magento\Integration\Model\Oauth\Consumer::class, '_dateHelper');
        $dateHelper->setAccessible(true);
        $dateHelper->setValue($this->consumerModel, $dateHelperMock);

        $this->consumerModel->setUpdatedAt(time());
        $this->assertFalse($this->consumerModel->isValidForTokenExchange());
    }
}
