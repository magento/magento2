<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Checkout;

use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Helper\Data as DirectoryHelper;
use PHPUnit\Framework\TestCase;
use Magento\Directory\Model\AllowedCountries;

class AttributeMergerTest extends TestCase
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * @var DirectoryHelper
     */
    private $directoryHelper;

    /**
     * @var AttributeMerger
     */
    private $attributeMerger;

    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->customerRepository = $this->createMock(CustomerRepository::class);
        $this->customerSession = $this->createMock(CustomerSession::class);
        $this->addressHelper = $this->createMock(AddressHelper::class);
        $this->directoryHelper = $this->createMock(DirectoryHelper::class);
        $this->allowedCountryReader = $this->createMock(AllowedCountries::class);

        $this->attributeMerger = new AttributeMerger(
            $this->addressHelper,
            $this->customerSession,
            $this->customerRepository,
            $this->directoryHelper,
            $this->allowedCountryReader
        );
    }

    /**
     * Tests of element attributes merging.
     *
     * @param String $validationRule - validation rule.
     * @param String $expectedValidation - expected mapped validation.
     * @dataProvider validationRulesDataProvider
     */
    public function testMerge(String $validationRule, String $expectedValidation): void
    {
        $elements = [
            'field' => [
                'visible' => true,
                'formElement' => 'input',
                'label' => __('City'),
                'value' =>  null,
                'sortOrder' => 1,
                'validation' => [
                    'input_validation' => $validationRule
                ],
            ]
        ];

        $actualResult = $this->attributeMerger->merge(
            $elements,
            'provider',
            'dataScope',
            ['field' => [
                'validation' => ['length' => true]
            ]
            ]
        );

        $expectedResult = [
            $expectedValidation => true,
            'length' => true
        ];

        self::assertEquals($expectedResult, $actualResult['field']['validation']);
    }

    /**
     * Provides possible validation types.
     *
     * @return array
     */
    public function validationRulesDataProvider(): array
    {
        return [
            ['alpha', 'validate-alpha'],
            ['numeric', 'validate-number'],
            ['alphanumeric', 'validate-alphanum'],
            ['alphanum-with-spaces', 'validate-alphanum-with-spaces'],
            ['url', 'validate-url'],
            ['email', 'email2'],
            ['length', 'validate-length']
        ];
    }
}
