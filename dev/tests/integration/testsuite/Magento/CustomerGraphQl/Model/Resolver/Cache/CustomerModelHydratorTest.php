<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Cache;

use Magento\Customer\Model\Data\Address;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CustomerModelHydratorTest extends TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData
     */
    private $resolverDataExtractor;

    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->resolverDataExtractor = $this->objectManager->get(\Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_with_addresses.php
     */
    public function testModelHydration(): void
    {
        $customerModel = $this->customerRepository->get('customer_with_addresses@test.com');
        $resolverData = $this->resolverDataExtractor->execute($customerModel);
        unset($resolverData['model']);
        /** @var CustomerModelHydrator $hydrator */
        $hydrator = $this->objectManager->get(CustomerModelHydrator::class);
        $hydrator->hydrate($resolverData);
        $this->assertInstanceOf(\Magento\Customer\Model\Data\Customer::class, $resolverData['model']);
        $assertionMap = [
            'model_id' => 'id',
            'model_group_id' => 'group_id',
            'firstname' => 'firstname',
            'lastname' => 'lastname'
        ];

        foreach ($assertionMap as $resolverDataField => $modelDataField) {
            $this->assertEquals(
                $resolverData[$resolverDataField],
                $resolverData['model']->{'get' . $this->camelize($modelDataField)}()
            );
        }

        $assertionMap = [
            'id' => 'id',
            'customer_id' => 'customer_id',
            'region_id' => 'region_id',
            'country_id' => 'country_id',
            'street' => 'street',
            'postcode' => 'postcode',
            'city' => 'city',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
        ];

        $addresses = $resolverData['model']->getAddresses();
        foreach ($addresses as $key => $address) {
            $this->assertInstanceOf(Address::class, $address);
            foreach ($assertionMap as $resolverDataField => $modelDataField)
            $this->assertEquals(
                $resolverData['addresses'][$key][$resolverDataField],
                $addresses[$key]->{'get' . $this->camelize($modelDataField)}()
            );
        }
    }

    /**
     * Transform snake case to camel case
     *
     * @param $string
     * @param $separator
     * @return string
     */
    private function camelize($string, $separator = '_')
    {
        return str_replace($separator, '', ucwords($string, $separator));
    }
}
