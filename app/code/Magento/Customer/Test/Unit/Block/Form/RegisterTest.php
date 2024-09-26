<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Form;

use Magento\Customer\Block\Form\Register;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Customer\Block\Form\Register.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RegisterTest extends TestCase
{
    /** Constants used by the various unit tests */
    const POST_ACTION_URL = 'http://localhost/index.php/customer/account/createpost';

    const LOGIN_URL = 'http://localhost/index.php/customer/account/login';

    const COUNTRY_ID = 'US';

    const FORM_DATA = 'form_data';

    const REGION_ATTRIBUTE_VALUE = 'California';

    const REGION_ID_ATTRIBUTE_CODE = 'region_id';

    const REGION_ID_ATTRIBUTE_VALUE = '12';

    /** @var MockObject|Data */
    private $directoryHelperMock;

    /** @var MockObject|ScopeConfigInterface */
    private $_scopeConfig;

    /** @var MockObject|Session */
    private $_customerSession;

    /** @var MockObject|Manager */
    private $_moduleManager;

    /** @var MockObject|Url */
    private $_customerUrl;

    /** @var Register */
    private $_block;

    /** @var MockObject|Config */
    private $newsletterConfig;

    protected function setUp(): void
    {
        $this->_scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->_moduleManager = $this->createMock(Manager::class);
        $this->directoryHelperMock = $this->createMock(Data::class);
        $this->_customerUrl = $this->createMock(Url::class);
        $this->_customerSession = $this->getMockBuilder(Session::class)
            ->addMethods(['getCustomerFormData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->newsletterConfig = $this->createMock(Config::class);
        $context = $this->createMock(Context::class);
        $context->expects($this->any())->method('getScopeConfig')->willReturn($this->_scopeConfig);

        $this->_block = new Register(
            $context,
            $this->directoryHelperMock,
            $this->getMockForAbstractClass(EncoderInterface::class, [], '', false),
            $this->createMock(\Magento\Framework\App\Cache\Type\Config::class),
            $this->createMock(CollectionFactory::class),
            $this->createMock(\Magento\Directory\Model\ResourceModel\Country\CollectionFactory::class),
            $this->_moduleManager,
            $this->_customerSession,
            $this->_customerUrl,
            [],
            $this->newsletterConfig
        );
    }

    /**
     * @param string $path
     * @param mixed $configValue
     *
     * @dataProvider getConfigProvider
     */
    public function testGetConfig($path, $configValue)
    {
        $this->_scopeConfig->expects($this->once())->method('getValue')->willReturn($configValue);
        $this->assertEquals($configValue, $this->_block->getConfig($path));
    }

    /**
     * @return array
     */
    public static function getConfigProvider()
    {
        return [
            ['/path/to/config/value', 'config value'],
            ['/path/to/config/value/that/does/not/exist', null]
        ];
    }

    public function testGetPostActionUrl()
    {
        $this->_customerUrl->expects(
            $this->once()
        )->method(
            'getRegisterPostUrl'
        )->willReturn(
            self::POST_ACTION_URL
        );
        $this->assertEquals(self::POST_ACTION_URL, $this->_block->getPostActionUrl());
    }

    /**
     * Tests the use case where 'back_url' has not been set on the block.
     */
    public function testGetBackUrlNullData()
    {
        $this->_customerUrl->expects(
            $this->once()
        )->method(
            'getLoginUrl'
        )->willReturn(
            self::LOGIN_URL
        );
        $this->assertEquals(self::LOGIN_URL, $this->_block->getBackUrl());
    }

    /**
     * Tests the use case where 'back_url' has been set on the block.
     */
    public function testGetBackUrlNotNullData()
    {
        $this->_block->setData('back_url', self::LOGIN_URL);
        $this->assertEquals(self::LOGIN_URL, $this->_block->getBackUrl());
    }

    /**
     * Form data has been set on the block so Form\Register::getFormData() simply returns it.
     */
    public function testGetFormDataNotNullFormData()
    {
        $data = new DataObject();
        $this->_block->setData(self::FORM_DATA, $data);
        $this->assertSame($data, $this->_block->getFormData());
    }

    /**
     * Form data has not been set on the block and there is no customer data in the customer session. So
     * we expect an empty \Magento\Framework\DataObject.
     */
    public function testGetFormDataNullFormData()
    {
        $data = new DataObject();
        $this->_customerSession->expects($this->once())->method('getCustomerFormData')->willReturn(null);
        $this->assertEquals($data, $this->_block->getFormData());
        $this->assertEquals($data, $this->_block->getData(self::FORM_DATA));
    }

    /**
     * Form data has not been set on the block, but there is customer data from the customer session.
     * The customer data is something other than 'region_id' so that code path is skipped.
     */
    public function testGetFormDataNullFormDataCustomerFormData()
    {
        $data = new DataObject();
        $data->setFirstname('John');
        $data->setCustomerData(1);
        $customerFormData = ['firstname' => 'John'];
        $this->_customerSession->expects(
            $this->once()
        )->method(
            'getCustomerFormData'
        )->willReturn(
            $customerFormData
        );
        $this->assertEquals($data, $this->_block->getFormData());
        $this->assertEquals($data, $this->_block->getData(self::FORM_DATA));
    }

    /**
     * Form data has not been set on the block, but there is customer data from the customer session.
     * The customer data is the 'region_id' so that code path is executed.
     */
    public function testGetFormDataCustomerFormDataRegionId()
    {
        $data = new DataObject();
        $data->setRegionId(self::REGION_ID_ATTRIBUTE_VALUE);
        $data->setCustomerData(1);
        $data[self::REGION_ID_ATTRIBUTE_CODE] = (int)self::REGION_ID_ATTRIBUTE_VALUE;
        $customerFormData = [self::REGION_ID_ATTRIBUTE_CODE => self::REGION_ID_ATTRIBUTE_VALUE];
        $this->_customerSession->expects(
            $this->once()
        )->method(
            'getCustomerFormData'
        )->willReturn(
            $customerFormData
        );
        $formData = $this->_block->getFormData();
        $this->assertEquals($data, $formData);
        $this->assertArrayHasKey(self::REGION_ID_ATTRIBUTE_CODE, $formData);
        $this->assertSame((int)self::REGION_ID_ATTRIBUTE_VALUE, $formData[self::REGION_ID_ATTRIBUTE_CODE]);
    }

    /**
     * Tests the Form\Register::getCountryId() use case where CountryId has been set on the form data
     * Object that has been set on the block.
     */
    public function testGetCountryIdFormData()
    {
        $formData = new DataObject();
        $formData->setCountryId(self::COUNTRY_ID);
        $this->_block->setData(self::FORM_DATA, $formData);
        $this->assertEquals(self::COUNTRY_ID, $this->_block->getCountryId());
    }

    /**
     * Tests the default country use case of parent::getCountryId() where CountryId has not been set
     * and the 'country_id' attribute has also not been set.
     */
    public function testGetCountryIdParentNullData()
    {
        $this->directoryHelperMock->expects(
            $this->once()
        )->method(
            'getDefaultCountry'
        )->willReturn(
            self::COUNTRY_ID
        );
        $this->assertEquals(self::COUNTRY_ID, $this->_block->getCountryId());
    }

    /**
     * Tests the parent::getCountryId() use case where CountryId has not been set and the 'country_id'
     * attribute code has been set on the block.
     */
    public function testGetCountryIdParentNotNullData()
    {
        $this->_block->setData('country_id', self::COUNTRY_ID);
        $this->assertEquals(self::COUNTRY_ID, $this->_block->getCountryId());
    }

    /**
     * Tests the first if conditional of Form\Register::getRegion(), which checks to see if Region has
     * been set on the form data Object that's set on the block.
     */
    public function testGetRegionByRegion()
    {
        $formData = new DataObject();
        $formData->setRegion(self::REGION_ATTRIBUTE_VALUE);
        $this->_block->setData(self::FORM_DATA, $formData);
        $this->assertSame(self::REGION_ATTRIBUTE_VALUE, $this->_block->getRegion());
    }

    /**
     * Tests the second if conditional of Form\Register::getRegion(), which checks to see if RegionId
     * has been set on the form data Object that's set on the block.
     */
    public function testGetRegionByRegionId()
    {
        $formData = new DataObject();
        $formData->setRegionId(self::REGION_ID_ATTRIBUTE_VALUE);
        $this->_block->setData(self::FORM_DATA, $formData);
        $this->assertSame(self::REGION_ID_ATTRIBUTE_VALUE, $this->_block->getRegion());
    }

    /**
     * Neither Region, nor RegionId have been set on the form data Object that's set on the block so a
     * null value is expected.
     */
    public function testGetRegionNull()
    {
        $formData = new DataObject();
        $this->_block->setData(self::FORM_DATA, $formData);
        $this->assertNull($this->_block->getRegion());
    }

    /**
     * @param boolean $isNewsletterEnabled
     * @param string $isNewsletterActive
     * @param boolean $expectedValue
     *
     * @dataProvider isNewsletterEnabledProvider
     */
    public function testIsNewsletterEnabled($isNewsletterEnabled, $isNewsletterActive, $expectedValue)
    {
        $this->_moduleManager->expects(
            $this->once()
        )->method(
            'isOutputEnabled'
        )->with(
            'Magento_Newsletter'
        )->willReturn(
            $isNewsletterEnabled
        );

        $this->newsletterConfig->expects(
            $this->any()
        )->method(
            'isActive'
        )->willReturn(
            $isNewsletterActive
        );

        $this->assertEquals($expectedValue, $this->_block->isNewsletterEnabled());
    }

    /**
     * @return array
     */
    public static function isNewsletterEnabledProvider()
    {
        return [[true, true, true], [true, false, false], [false, true, false], [false, false, false]];
    }

    /**
     * This test is designed to execute all code paths of Form\Register::getFormData() when testing the
     * Form\Register::restoreSessionData() method.
     */
    public function testRestoreSessionData()
    {
        $data = new DataObject();
        $data->setRegionId(self::REGION_ID_ATTRIBUTE_VALUE);
        $data->setCustomerData(1);
        $data[self::REGION_ID_ATTRIBUTE_CODE] = (int)self::REGION_ID_ATTRIBUTE_VALUE;
        $customerFormData = [self::REGION_ID_ATTRIBUTE_CODE => self::REGION_ID_ATTRIBUTE_VALUE];
        $this->_customerSession->expects(
            $this->once()
        )->method(
            'getCustomerFormData'
        )->willReturn(
            $customerFormData
        );
        $form = $this->createMock(Form::class);
        $request = $this->getMockForAbstractClass(RequestInterface::class, [], '', false);
        $formData = $this->_block->getFormData();
        $form->expects(
            $this->once()
        )->method(
            'prepareRequest'
        )->with(
            $formData->getData()
        )->willReturn(
            $request
        );
        $form->expects(
            $this->once()
        )->method(
            'extractData'
        )->with(
            $request,
            null,
            false
        )->willReturn(
            $customerFormData
        );
        $form->expects($this->once())->method('restoreData')->willReturn($customerFormData);
        $block = $this->_block->restoreSessionData($form, null, false);
        $this->assertSame($this->_block, $block);
        $this->assertEquals($data, $block->getData(self::FORM_DATA));
    }

    /**
     * Test get minimum password length
     */
    public function testGetMinimumPasswordLength()
    {
        $this->_scopeConfig->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH
        )->willReturn(
            6
        );
        $this->assertEquals(6, $this->_block->getMinimumPasswordLength());
    }

    /**
     * Test get required character classes number
     */
    public function testGetRequiredCharacterClassesNumber()
    {
        $this->_scopeConfig->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER
        )->willReturn(
            3
        );
        $this->assertEquals(3, $this->_block->getRequiredCharacterClassesNumber());
    }
}
