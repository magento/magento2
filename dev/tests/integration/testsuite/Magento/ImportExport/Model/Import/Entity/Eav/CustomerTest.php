<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\ImportExport\Model\Import\Entity\Eav;

/**
 * Test for class \Magento\ImportExport\Model\Import\Entity\Eav\Customer which covers validation logic
 *
 * @magentoDataFixture Magento/ImportExport/_files/customers.php
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Model object which used for tests
     *
     * @var \Magento\ImportExport\Model\Import\Entity\Eav\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * Customer data
     *
     * @var array
     */
    protected $_customerData;

    /**
     * Create all necessary data for tests
     */
    protected function setUp()
    {
        parent::setUp();

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\ImportExport\Model\Import\Entity\Eav\Customer');
        $this->_model->setParameters(array(
            'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE
        ));

        $propertyAccessor = new \ReflectionProperty($this->_model, '_messageTemplates');
        $propertyAccessor->setAccessible(true);
        $propertyAccessor->setValue($this->_model, array());

        $this->_customerData = array(
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'group_id' => 1,
            \Magento\ImportExport\Model\Import\Entity\Eav\Customer::COLUMN_EMAIL => 'customer@example.com',
            \Magento\ImportExport\Model\Import\Entity\Eav\Customer::COLUMN_WEBSITE => 'base',
            \Magento\ImportExport\Model\Import\Entity\Eav\Customer::COLUMN_STORE => 'default',
            'store_id' => 1,
            'website_id' => 1,
            'password' => 'password',
        );
    }

    /**
     * Test which check duplicated data validation
     */
    public function testValidateRowDuplicateEmail()
    {
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(0, $this->_model->getErrorsCount());

        $this->_customerData[\Magento\ImportExport\Model\Import\Entity\Eav\Customer::COLUMN_EMAIL] =
            strtoupper($this->_customerData[\Magento\ImportExport\Model\Import\Entity\Eav\Customer::COLUMN_EMAIL]);
        $this->_model->validateRow($this->_customerData, 1);
        $this->assertEquals(1, $this->_model->getErrorsCount());
        $this->assertArrayHasKey(\Magento\ImportExport\Model\Import\Entity\Eav\Customer::ERROR_DUPLICATE_EMAIL_SITE,
            $this->_model->getErrorMessages());
    }

    /**
     * Test which check validation of customer email
     */
    public function testValidateRowInvalidEmail()
    {
        $this->_customerData[\Magento\ImportExport\Model\Import\Entity\Eav\Customer::COLUMN_EMAIL]
            = 'wrong_email@format';
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(1, $this->_model->getErrorsCount());
        $this->assertArrayHasKey(\Magento\ImportExport\Model\Import\Entity\Eav\Customer::ERROR_INVALID_EMAIL,
            $this->_model->getErrorMessages()
        );
    }

    /**
     * Test which check validation of website data
     */
    public function testValidateRowInvalidWebsite()
    {
        $this->_customerData[\Magento\ImportExport\Model\Import\Entity\Eav\Customer::COLUMN_WEBSITE]
            = 'not_existing_web_site';
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(1, $this->_model->getErrorsCount());
        $this->assertArrayHasKey(\Magento\ImportExport\Model\Import\Entity\Eav\Customer::ERROR_INVALID_WEBSITE,
            $this->_model->getErrorMessages()
        );
    }

    /**
     * Test which check validation of store data
     */
    public function testValidateRowInvalidStore()
    {
        $this->_customerData[\Magento\ImportExport\Model\Import\Entity\Eav\Customer::COLUMN_STORE]
            = 'not_existing_web_store';
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(1, $this->_model->getErrorsCount());
        $this->assertArrayHasKey(\Magento\ImportExport\Model\Import\Entity\Eav\Customer::ERROR_INVALID_STORE,
            $this->_model->getErrorMessages()
        );
    }

    /**
     * Test which check validation of password length - incorrect case
     */
    public function testValidateRowPasswordLengthIncorrect()
    {
        $this->_customerData['password'] = '12345';
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(1, $this->_model->getErrorsCount());
        $this->assertArrayHasKey(\Magento\ImportExport\Model\Import\Entity\Eav\Customer::ERROR_PASSWORD_LENGTH,
            $this->_model->getErrorMessages()
        );
    }

    /**
     * Test which check validation of password length - correct case
     */
    public function testValidateRowPasswordLengthCorrect()
    {
        $this->_customerData['password'] = '1234567890';
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(0, $this->_model->getErrorsCount());
    }

    /**
     * Test which check validation of required fields
     */
    public function testValidateRowAttributeRequired()
    {
        unset($this->_customerData['firstname']);
        unset($this->_customerData['lastname']);
        unset($this->_customerData['group_id']);

        $this->_model->validateRow($this->_customerData, 0);
        $this->assertEquals(0, $this->_model->getErrorsCount());

        $this->_customerData[\Magento\ImportExport\Model\Import\Entity\Eav\Customer::COLUMN_EMAIL]
            = 'new.customer@example.com';
        $this->_model->validateRow($this->_customerData, 1);
        $this->assertGreaterThan(0, $this->_model->getErrorsCount());
        $this->assertArrayHasKey(\Magento\ImportExport\Model\Import\Entity\Eav\Customer::ERROR_VALUE_IS_REQUIRED,
            $this->_model->getErrorMessages()
        );
    }

    /**
     * Check customer email validation for delete behavior
     *
     * @covers \Magento\ImportExport\Model\Import\Entity\Eav\Customer::validateRow
     */
    public function testValidateEmailForDeleteBehavior()
    {
        $this->_customerData[\Magento\ImportExport\Model\Import\Entity\Eav\Customer::COLUMN_EMAIL]
            = 'new.customer@example.com';

        $this->_model->setParameters(array(
            'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE
        ));
        $this->_model->validateRow($this->_customerData, 0);
        $this->assertGreaterThan(0, $this->_model->getErrorsCount());
        $this->assertArrayHasKey(\Magento\ImportExport\Model\Import\Entity\Eav\Customer::ERROR_CUSTOMER_NOT_FOUND,
            $this->_model->getErrorMessages()
        );
    }
}
