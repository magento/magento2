<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Data;

use Magento\Customer\Model\Data\Customer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for customer data model
 */
class CustomerTest extends TestCase
{
    /** @var Customer */
    protected $model;

    /** @var ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->model = $this->objectManager->getObject(Customer::class);
    }

    /**
     * Test getGroupId()
     *
     * @return void
     */
    public function testGetGroupId()
    {
        $testGroupId = 3;
        $this->model->setGroupId($testGroupId);
        $this->assertEquals($testGroupId, $this->model->getGroupId());
    }

    /**
     * Test getCreatedIn()
     *
     * @param array|string $options
     * @param array $expectedResult
     *
     * @dataProvider getCreatedInDataProvider
     *
     * @return void
     */
    public function testGetCreatedIn($options, $expectedResult)
    {
        $optionsCount = count($options);
        $expectedCount = count($expectedResult);

        for ($i = 0; $i < $optionsCount; $i++) {
            $this->model->setCreatedIn($options[$i]);
            for ($j = $i; $j < $expectedCount; $j++) {
                $this->assertEquals($expectedResult[$j], $this->model->getCreatedIn());
                break;
            }
        }
    }

    /**
     * Data provider for testGetCreatedIn
     *
     * @return array
     */
    public function getCreatedInDataProvider()
    {
        return [
            'array' => [
                'options' => ['Default', 'Admin', 'US'],
                'expectedResult' => ['Default', 'Admin', 'US']
            ]
        ];
    }

    /**
     * Test getDisableAutoGroupChange(), method for disable auto group change flag.
     *
     * @return void
     */
    public function testGetDisableAutoGroupChangeReturnInt()
    {
        $testDisableAutoGroupFlag = 0;
        $this->model->setDisableAutoGroupChange($testDisableAutoGroupFlag);
        $this->assertEquals((int)$testDisableAutoGroupFlag, $this->model->getDisableAutoGroupChange());
    }

    /**
     * Test getDisableAutoGroupChange(), method for disable auto group change flag.
     *
     * @return void
     */
    public function testGetDisableAutoGroupChangeReturnNull()
    {
        $testDisableAutoGroupFlag = null;
        $this->model->setDisableAutoGroupChange($testDisableAutoGroupFlag);
        $this->assertNull($testDisableAutoGroupFlag, $this->model->getDisableAutoGroupChange());
    }

    /**
     * Test getDefaultBilling(), method for default billing address id.
     *
     * @return void
     */
    public function testGetDefaultBilling()
    {
        $defaultAddressId = 45;
        $this->model->setDefaultBilling($defaultAddressId);
        $this->assertEquals($defaultAddressId, $this->model->getDefaultBilling());
    }

    /**
     * Test getDefaultShipping(), method for default shipping address id.
     *
     * @return void
     */
    public function testGetDefaultShipping()
    {
        $defaultAddressId = 21;
        $this->model->setDefaultShipping($defaultAddressId);
        $this->assertEquals($defaultAddressId, $this->model->getDefaultShipping());
    }

    /**
     * Test getCreatedAt(), method for get created at date and time
     *
     * @return void
     */
    public function testGetCreatedAt()
    {
        $createdAt = "2020-02-24 09:35:26";
        $this->model->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $this->model->getCreatedAt());
    }

    /**
     * Test getUpdatedAt(), method for get updated at date and time
     *
     * @return void
     */
    public function testGetUpdatedAt()
    {
        $updatedAt = "2020-01-30 02:42:23";
        $this->model->setUpdatedAt($updatedAt);
        $this->assertEquals($updatedAt, $this->model->getUpdatedAt());
    }

    /**
     * Test getDob(), method for get date of birthday
     *
     * @return void
     */
    public function testGetDob()
    {
        $dobDate = "2000-11-03";
        $this->model->setDob($dobDate);
        $this->assertEquals($dobDate, $this->model->getDob());
    }

    /**
     * Test getDob(), method for get date of birthday
     *
     * @return void
     */
    public function testGetEmail()
    {
        $testEmail = "customer@example.com";
        $this->model->setEmail($testEmail);
        $this->assertEquals($testEmail, $this->model->getEmail());
    }

    /**
     * Test getFirstname(), method for get customer firstname
     *
     * @return void
     */
    public function testGetFirstname()
    {
        $testFirstName = "Firstname";
        $this->model->setFirstname($testFirstName);
        $this->assertEquals($testFirstName, $this->model->getFirstname());
    }

    /**
     * Test getGender(), method for get customer gender
     *
     * @return void
     */
    public function testGetGender()
    {
        $testFirstName = "female";
        $this->model->setGender($testFirstName);
        $this->assertEquals($testFirstName, $this->model->getGender());
    }

    /**
     * Test getId(), method for get customer id
     *
     * @return void
     */
    public function testGetId()
    {
        $testId = 2;
        $this->model->setId($testId);
        $this->assertEquals($testId, $this->model->getId());
    }

    /**
     * Test getLastname(), method for get customer lastname
     *
     * @return void
     */
    public function testGetLastname()
    {
        $testLastName = "Lastname";
        $this->model->setLastname($testLastName);
        $this->assertEquals($testLastName, $this->model->getLastname());
    }

    /**
     * Test getMiddlename(), method for get customer middlename
     *
     * @return void
     */
    public function testGetMiddlename()
    {
        $testMiddleName = "Middlename";
        $this->model->setMiddlename($testMiddleName);
        $this->assertEquals($testMiddleName, $this->model->getMiddlename());
    }

    /**
     * Test getPrefix(), method for get customer prefix
     *
     * @return void
     */
    public function testGetPrefix()
    {
        $testPrefix = "Mr";
        $this->model->setPrefix($testPrefix);
        $this->assertEquals($testPrefix, $this->model->getPrefix());
    }

    /**
     * Test getStoreId(), method for get store id
     *
     * @return void
     */
    public function testGetStoreId()
    {
        $testStoreId = 4;
        $this->model->setStoreId($testStoreId);
        $this->assertEquals($testStoreId, $this->model->getStoreId());
    }

    /**
     * Test getSuffix(), method for get customer suffix
     *
     * @return void
     */
    public function testGetSuffix()
    {
        $testSuffix = "test";
        $this->model->setSuffix($testSuffix);
        $this->assertEquals($testSuffix, $this->model->getSuffix());
    }

    /**
     * Test getTaxvat(), method for get VAT
     *
     * @return void
     */
    public function testGetTaxvat()
    {
        $testVat = "testVat";
        $this->model->setTaxvat($testVat);
        $this->assertEquals($testVat, $this->model->getTaxvat());
    }

    /**
     * Test getWebsiteId(), method for get website id
     *
     * @return void
     */
    public function testGetWebsiteId()
    {
        $testWebsiteId = 11;
        $this->model->setWebsiteId($testWebsiteId);
        $this->assertEquals($testWebsiteId, $this->model->getWebsiteId());
    }

    /**
     * Test getDisableAutoGroupChange(), method for get auto group change flag
     *
     * @return void
     */
    public function testGetDisableAutoGroupChange()
    {
        $isAutoGroupChangeAllowed = 1;
        $this->model->setDisableAutoGroupChange($isAutoGroupChangeAllowed);
        $this->assertEquals($isAutoGroupChangeAllowed, $this->model->getDisableAutoGroupChange());
    }
}
