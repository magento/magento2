<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Form;

use Magento\Customer\Block\Form\Register;
use Magento\Customer\Model\AccountManagement;

/**
 * Test class for \Magento\Customer\Block\Form\Register.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RegisterTest extends \PHPUnit\Framework\TestCase
{
    /** Constants used by the various unit tests */
    const POST_ACTION_URL = 'http://localhost/index.php/customer/account/createpost';

    const LOGIN_URL = 'http://localhost/index.php/customer/account/login';

    const COUNTRY_ID = 'US';

    const FORM_DATA = 'form_data';

    const REGION_ATTRIBUTE_VALUE = 'California';

    const REGION_ID_ATTRIBUTE_CODE = 'region_id';

    const REGION_ID_ATTRIBUTE_VALUE = '12';

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Directory\Helper\Data */
    private $directoryHelperMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\App\Config\ScopeConfigInterface */
    private $_scopeConfig;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Customer\Model\Session */
    private $_customerSession;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Module\Manager */
    private $_moduleManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Customer\Model\Url */
    private $_customerUrl;

    /** @var Register */
    private $_block;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Newsletter\Model\Config */
    private $newsletterConfig;

    protected function setUp(): void
    {
        $this->_scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_moduleManager = $this->createMock(\Magento\Framework\Module\Manager::class);
        $this->directoryHelperMock = $this->createMock(\Magento\Directory\Helper\Data::class);
        $this->_customerUrl = $this->createMock(\Magento\Customer\Model\Url::class);
        $this->_customerSession = $this->createPartialMock(
            \Magento\Customer\Model\Session::class,
            ['getCustomerFormData']
        );
        $this->newsletterConfig = $this->createMock(\Magento\Newsletter\Model\Config::class);
        $context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $context->expects($this->any())->method('getScopeConfig')->willReturn($this->_scopeConfig);

        $this->_block = new \Magento\Customer\Block\Form\Register(
            $context,
            $this->directoryHelperMock,
            $this->getMockForAbstractClass(\Magento\Framework\Json\EncoderInterface::class, [], '', false),
            $this->createMock(\Magento\Framework\App\Cache\Type\Config::class),
            $this->createMock(\Magento\Directory\Model\ResourceModel\Region\CollectionFactory::class),
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
    public function getConfigProvider()
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
        $data = new \Magento\Framework\DataObject();
        $this->_block->setData(self::FORM_DATA, $data);
        $this->assertSame($data, $this->_block->getFormData());
    }

    /**
     * Form data has not been set on the block and there is no customer data in the customer session. So
     * we expect an empty \Magento\Framework\DataObject.
     */
    public function testGetFormDataNullFormData()
    {
        $data = new \Magento\Framework\DataObject();
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
        $data = new \Magento\Framework\DataObject();
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
        $data = new \Magento\Framework\DataObject();
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
        $this->assertTrue(isset($formData[self::REGION_ID_ATTRIBUTE_CODE]));
        $this->assertSame((int)self::REGION_ID_ATTRIBUTE_VALUE, $formData[self::REGION_ID_ATTRIBUTE_CODE]);
    }

    /**
     * Tests the Form\Register::getCountryId() use case where CountryId has been set on the form data
     * Object that has been set on the block.
     */
    public function testGetCountryIdFormData()
    {
        $formData = new \Magento\Framework\DataObject();
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
        $formData = new \Magento\Framework\DataObject();
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
        $formData = new \Magento\Framework\DataObject();
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
        $formData = new \Magento\Framework\DataObject();
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
    public function isNewsletterEnabledProvider()
    {
        return [[true, true, true], [true, false, false], [false, true, false], [false, false, false]];
    }

    /**
     * This test is designed to execute all code paths of Form\Register::getFormData() when testing the
     * Form\Register::restoreSessionData() method.
     */
    public function testRestoreSessionData()
    {
        $data = new \Magento\Framework\DataObject();
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
        $form = $this->createMock(\Magento\Customer\Model\Metadata\Form::class);
        $request = $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class, [], '', false);
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
