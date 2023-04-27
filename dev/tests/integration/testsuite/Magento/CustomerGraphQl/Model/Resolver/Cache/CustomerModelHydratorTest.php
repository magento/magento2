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

    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testModelHydration()
    {
        $resolverData = $this->getCustomerResolverData();
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

    /**
     * @return array
     */
    private function getCustomerResolverData()
    {
        return [
            'id' => null,
            'group_id' => null,
            'default_billing' => '1',
            'default_shipping' => '1',
            'created_at' => '2023-04-18 13:15:13',
            'updated_at' => '2023-04-25 18:32:27',
            'created_in' => 'Default Store View',
            'email' => 'user@example.com',
            'firstname' => 'User',
            'lastname' => 'Lastname',
            'store_id' => 1,
            'website_id' => 1,
            'addresses' => [
                [
                    'id' => 1,
                    'customer_id' => 3,
                    'region' => [
                        'region_code' => 'TX',
                        'region' => 'Texas',
                        'region_id' => 57,
                    ],
                    'region_id' => 57,
                    'country_id' => 'US',
                    'street' => [
                        0 => '11501 Domain Dr',
                    ],
                    'telephone' => '12345683748',
                    'postcode' => '78758',
                    'city' => 'Austin',
                    'firstname' => 'User',
                    'lastname' => 'Lastname',
                    'default_shipping' => true,
                    'default_billing' => true,
                ],
                [
                    'id' => 2,
                    'customer_id' => 3,
                    'region' => [
                        'region_code' => 'TX',
                        'region' => 'Texas',
                        'region_id' => 57,
                    ],
                    'region_id' => 57,
                    'country_id' => 'US',
                    'street' => [
                        0 => '11505 Domain Dr',
                    ],
                    'telephone' => '15121234567',
                    'postcode' => '78717',
                    'city' => 'Austin',
                    'firstname' => 'User',
                    'lastname' => 'Lastname',
                    'default_shipping' => false,
                    'default_billing' => false,
                ],
            ],
            'model_id' => '3',
            'model_group_id' => '1',
            'model' => null,
        ];
    }
}
