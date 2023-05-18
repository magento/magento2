<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Cache;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Data\Address;
use Magento\Customer\Model\Data\Customer;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\CustomerGraphQl\Model\Resolver\Cache\Customer\ModelDehydrator;
use Magento\CustomerGraphQl\Model\Resolver\Cache\Customer\ModelHydrator;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CustomerModelHydratorDehydratorTest extends TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ExtractCustomerData
     */
    private $resolverDataExtractor;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->resolverDataExtractor = $this->objectManager->get(ExtractCustomerData::class);
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_with_addresses.php
     */
    public function testModelHydration(): void
    {
        $customerModel = $this->customerRepository->get('customer_with_addresses@test.com');
        $resolverData = $this->resolverDataExtractor->execute($customerModel);
        /** @var ModelDehydrator $dehydrator */
        $dehydrator = $this->objectManager->get(ModelDehydrator::class);
        $dehydrator->dehydrate($resolverData);

        $serializedData = $this->serializer->serialize($resolverData);
        $resolverData = $this->serializer->unserialize($serializedData);

        /** @var ModelHydrator $hydrator */
        $hydrator = $this->objectManager->get(ModelHydrator::class);
        $hydrator->hydrate($resolverData);
        $this->assertInstanceOf(Customer::class, $resolverData['model']);
        $assertionMap = [
            'model_id' => 'id',
            'firstname' => 'firstname',
            'lastname' => 'lastname'
        ];

        foreach ($assertionMap as $resolverDataField => $modelDataField) {
            $this->assertEquals(
                $resolverData[$resolverDataField],
                $resolverData['model']->{'get' . $this->camelize($modelDataField)}()
            );
        }

        $this->assertEquals(
            $customerModel->getExtensionAttributes(),
            $resolverData['model']->getExtensionAttributes()
        );

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
            foreach ($assertionMap as $resolverDataField => $modelDataField) {
                $this->assertEquals(
                    $resolverData['addresses'][$key][$resolverDataField],
                    $address->{'get' . $this->camelize($modelDataField)}()
                );
            }
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
