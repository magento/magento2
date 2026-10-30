<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateDobOnSaveDataTest extends TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $om;
    /** @var CustomerRepositoryInterface */
    private $customerRepo;
    /** @var EavConfig */
    private $eavConfig;
    /** @var AttributeResource */
    private $attributeResource;
    /** @var JsonSerializer */
    private $serializer;

    protected function setUp(): void
    {
        $this->om = Bootstrap::getObjectManager();
        $this->customerRepo = $this->om->get(CustomerRepositoryInterface::class);
        $this->eavConfig = $this->om->get(EavConfig::class);
        $this->attributeResource = $this->om->get(AttributeResource::class);
        $this->serializer = $this->om->get(JsonSerializer::class);
    }

    #[
        DataFixture(CustomerFixture::class, as: 'cust')
    ]
    public function testDobBeforeMinThrows(): void
    {
        $this->setDobRules(['date_range_min' => '1980-01-01', 'date_range_max' => '2000-12-31']);

        $customer = $this->getCustomerFromFixture('cust');
        $customer->setDob('1979-12-31');

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('on or after 1980-01-01');
        $this->customerRepo->save($customer);
    }

    #[
        DataFixture(CustomerFixture::class, as: 'cust')
    ]
    public function testDobAfterMaxThrows(): void
    {
        $this->setDobRules(['date_range_min' => '1980-01-01', 'date_range_max' => '2000-12-31']);

        $customer = $this->getCustomerFromFixture('cust');
        $customer->setDob('2001-01-01');

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('on or before 2000-12-31');
        $this->customerRepo->save($customer);
    }

    #[
        DataFixture(CustomerFixture::class, as: 'cust')
    ]
    public function testDobWithinRangeSucceeds(): void
    {
        $this->setDobRules(['date_range_min' => '1980-01-01', 'date_range_max' => '2000-12-31']);

        $customer = $this->getCustomerFromFixture('cust');
        $customer->setDob('1990-06-15');

        $saved = $this->customerRepo->save($customer);
        $this->assertNotEmpty($saved->getId());
        $this->assertSame('1990-06-15', $saved->getDob());
    }

    #[
        DataFixture(CustomerFixture::class, as: 'cust')
    ]
    public function testDobWithMillisecondRulesThrows(): void
    {
        $min = (new \DateTimeImmutable('1980-01-01', new \DateTimeZone('UTC')))->getTimestamp() * 1000;
        $max = (new \DateTimeImmutable('2000-12-31', new \DateTimeZone('UTC')))->getTimestamp() * 1000;
        $this->setDobRules(['date_range_min' => $min, 'date_range_max' => $max]);

        $customer = $this->getCustomerFromFixture('cust');
        $customer->setDob('1979-12-31');

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('on or after 1980-01-01');
        $this->customerRepo->save($customer);
    }

    #[
        DataFixture(CustomerFixture::class, as: 'cust')
    ]
    public function testSaveWithoutDobSucceeds(): void
    {
        $this->setDobRules(['date_range_min' => '1980-01-01', 'date_range_max' => '2000-12-31']);

        $customer = $this->getCustomerFromFixture('cust');
        $customer->setDob(null);

        $saved = $this->customerRepo->save($customer);
        $this->assertNotEmpty($saved->getId());
        $this->assertNull($saved->getDob());
    }

    /**
     * @param string $key
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomerFromFixture(string $key): CustomerInterface
    {
        $stored = DataFixtureStorageManager::getStorage()->get($key);
        $id = is_object($stored) && method_exists($stored, 'getId') ? (int)$stored->getId() : (int)$stored;
        return $this->customerRepo->getById($id);
    }

    /**
     * @param array $rules
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setDobRules(array $rules): void
    {
        $attr = $this->eavConfig->getAttribute('customer', 'dob');
        $attr->setData('validate_rules', $this->serializer->serialize($rules));
        $this->attributeResource->save($attr);
        $this->eavConfig->clear();
    }
}
