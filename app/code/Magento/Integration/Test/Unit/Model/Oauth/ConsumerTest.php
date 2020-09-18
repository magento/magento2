<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Oauth;

use Laminas\Validator\Uri as LaminasUriValidator;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Url\Validator as UrlValidator;
use Magento\Integration\Helper\Oauth\Data;
use Magento\Integration\Model\Oauth\Consumer;
use Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength;
use Magento\Integration\Model\Oauth\Consumer\Validator\KeyLengthFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Integration\Model\Oauth\Consumer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumerTest extends TestCase
{
    /**
     * @var Consumer
     */
    protected $consumerModel;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var KeyLength
     */
    protected $keyLengthValidator;

    /**
     * @var KeyLengthFactory
     */
    protected $keyLengthValidatorFactory;

    /**
     * @var UrlValidator
     */
    protected $urlValidator;

    /**
     * @var Data|MockObject
     */
    protected $oauthDataMock;

    /**
     * @var AbstractResource|MockObject
     */
    protected $resourceMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resourceCollectionMock;

    /**
     * @var array
     */
    protected $validDataArray;

    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->getMockForAbstractClass(
            ManagerInterface::class,
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

        $this->registryMock = $this->createMock(Registry::class);

        $this->keyLengthValidator = new KeyLength();

        $this->urlValidator = new UrlValidator(new LaminasUriValidator());

        $this->oauthDataMock = $this->createPartialMock(
            Data::class,
            ['getConsumerExpirationPeriod']
        );
        $this->oauthDataMock->expects($this->any())
            ->method('getConsumerExpirationPeriod')
            ->willReturn(Data::CONSUMER_EXPIRATION_PERIOD_DEFAULT);

        $this->resourceMock = $this->getMockBuilder(
            \Magento\Integration\Model\ResourceModel\Oauth\Consumer::class
        )->addMethods(['selectByCompositeKey', 'deleteOldEntries'])
            ->onlyMethods(['getIdFieldName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceCollectionMock = $this->createMock(AbstractDb::class);
        $this->consumerModel = new Consumer(
            $this->contextMock,
            $this->registryMock,
            $this->keyLengthValidator,
            $this->urlValidator,
            $this->oauthDataMock,
            $this->resourceMock,
            $this->resourceCollectionMock
        );

        $this->validDataArray = [
            'key' => md5(uniqid()), // phpcs:ignore Magento2.Security.InsecureFunction
            'secret' => md5(uniqid()), // phpcs:ignore Magento2.Security.InsecureFunction
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

    public function testValidateInvalidData()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid Callback URL');
        $this->validDataArray['callback_url'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    public function testValidateInvalidCallback()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid Callback URL');
        $this->validDataArray['callback_url'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    public function testValidateInvalidRejectedCallback()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid Rejected Callback URL');
        $this->validDataArray['rejected_callback_url'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    public function testValidateInvalidConsumerKey()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Consumer Key \'invalid\' is less than 32 characters long');
        $this->validDataArray['key'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    public function testValidateInvalidConsumerSecret()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Consumer Secret \'invalid\' is less than 32 characters long');
        $this->validDataArray['secret'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    public function testGetConsumerExpirationPeriodValid()
    {
        $dateHelperMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateHelperMock->expects($this->at(0))->method('gmtTimestamp')->willReturn(time());
        $dateHelperMock->expects($this->at(1))->method('gmtTimestamp')->willReturn(time() - 100);

        $dateHelper = new \ReflectionProperty(Consumer::class, '_dateHelper');
        $dateHelper->setAccessible(true);
        $dateHelper->setValue($this->consumerModel, $dateHelperMock);

        $this->consumerModel->setUpdatedAt((string)time());
        $this->assertTrue($this->consumerModel->isValidForTokenExchange());
    }

    public function testGetConsumerExpirationPeriodExpired()
    {
        $dateHelperMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateHelperMock->expects($this->at(0))->method('gmtTimestamp')->willReturn(time());
        $dateHelperMock->expects($this->at(1))->method('gmtTimestamp')->willReturn(time() - 1000);

        $dateHelper = new \ReflectionProperty(Consumer::class, '_dateHelper');
        $dateHelper->setAccessible(true);
        $dateHelper->setValue($this->consumerModel, $dateHelperMock);

        $this->consumerModel->setUpdatedAt((string)time());
        $this->assertFalse($this->consumerModel->isValidForTokenExchange());
    }
}
