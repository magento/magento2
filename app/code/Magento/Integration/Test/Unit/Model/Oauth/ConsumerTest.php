<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\Oauth;

use Magento\Framework\Url\Validator as UrlValidator;
use Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength;

/**
 * Test for \Magento\Integration\Model\Oauth\Consumer
 */
class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Model\Oauth\Consumer
     */
    protected $consumerModel;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Integration\Helper\Oauth\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $oauthDataMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollectionMock;

    /**
     * @var array
     */
    protected $validDataArray;

    protected function setUp()
    {
        $this->contextMock = $this->getMock(
            'Magento\Framework\Model\Context',
            ['getEventDispatcher'],
            [],
            '',
            false
        );
        $eventManagerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Event\ManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->will($this->returnValue($eventManagerMock));

        $this->registryMock = $this->getMock(
            'Magento\Framework\Registry',
            [],
            [],
            '',
            false
        );

        $this->keyLengthValidator = new KeyLength();

        $this->urlValidator = new UrlValidator();

        $this->oauthDataMock = $this->getMock(
            'Magento\Integration\Helper\Oauth\Data',
            ['getConsumerExpirationPeriod'],
            [],
            '',
            false
        );
        $this->oauthDataMock->expects($this->any())
            ->method('getConsumerExpirationPeriod')
            ->will($this->returnValue(\Magento\Integration\Helper\Oauth\Data::CONSUMER_EXPIRATION_PERIOD_DEFAULT));

        $this->resourceMock = $this->getMock(
            'Magento\Integration\Model\ResourceModel\Oauth\Consumer',
            ['getIdFieldName', 'selectByCompositeKey', 'deleteOldEntries'],
            [],
            '',
            false,
            true,
            true
        );
        $this->resourceCollectionMock = $this->getMock(
            'Magento\Framework\Data\Collection\AbstractDb',
            [],
            [],
            '',
            false
        );
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid Callback URL
     */
    public function testValidateInvalidData()
    {
        $this->validDataArray['callback_url'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid Callback URL
     */
    public function testValidateInvalidCallback()
    {
        $this->validDataArray['callback_url'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid Rejected Callback URL
     */
    public function testValidateInvalidRejectedCallback()
    {
        $this->validDataArray['rejected_callback_url'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Consumer Key 'invalid' is less than 32 characters long
     */
    public function testValidateInvalidConsumerKey()
    {
        $this->validDataArray['key'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Consumer Secret 'invalid' is less than 32 characters long
     */
    public function testValidateInvalidConsumerSecret()
    {
        $this->validDataArray['secret'] = 'invalid';
        $this->consumerModel->setData($this->validDataArray);
        $this->consumerModel->validate();
    }

    public function testGetConsumerExpirationPeriodValid()
    {
        $dateHelperMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $dateHelperMock->expects($this->at(0))->method('gmtTimestamp')->willReturn(time());
        $dateHelperMock->expects($this->at(1))->method('gmtTimestamp')->willReturn(time() - 100);

        $dateHelper = new \ReflectionProperty('Magento\Integration\Model\Oauth\Consumer', '_dateHelper');
        $dateHelper->setAccessible(true);
        $dateHelper->setValue($this->consumerModel, $dateHelperMock);

        $this->consumerModel->setUpdatedAt(time());
        $this->assertTrue($this->consumerModel->isValidForTokenExchange());
    }

    public function testGetConsumerExpirationPeriodExpired()
    {
        $dateHelperMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $dateHelperMock->expects($this->at(0))->method('gmtTimestamp')->willReturn(time());
        $dateHelperMock->expects($this->at(1))->method('gmtTimestamp')->willReturn(time() - 1000);

        $dateHelper = new \ReflectionProperty('Magento\Integration\Model\Oauth\Consumer', '_dateHelper');
        $dateHelper->setAccessible(true);
        $dateHelper->setValue($this->consumerModel, $dateHelperMock);

        $this->consumerModel->setUpdatedAt(time());
        $this->assertFalse($this->consumerModel->isValidForTokenExchange());
    }
}
