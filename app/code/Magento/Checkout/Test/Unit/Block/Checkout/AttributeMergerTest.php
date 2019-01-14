<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Checkout;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Checkout\Block\Checkout\AttributeMerger;

class AttributeMergerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerRepository
     */
    private $customerRepositoryMock;

    /**
     * @var CustomerSession
     */
    private $customerSessionMock;

    /**
     * @var AddressHelper
     */
    private $addressHelperMock;

    /**
     * @var DirectoryHelper
     */
    private $directoryHelperMock;

    /**
     * @var AttributeMerger
     */
    private $attributeMerger;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {

        $this->customerRepositoryMock = $this->createMock(CustomerRepository::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->addressHelperMock = $this->createMock(AddressHelper::class);
        $this->directoryHelperMock = $this->createMock(DirectoryHelper::class);

        $this->attributeMerger = new AttributeMerger(
            $this->addressHelperMock,
            $this->customerSessionMock,
            $this->customerRepositoryMock,
            $this->directoryHelperMock
        );
    }

    /**
     * Tests of element attributes merging.
     *
     * @param string $validationRule
     * @param string $expectedValidation
     * @return void
     * @dataProvider validationRulesDataProvider
     */
    public function testMerge($validationRule, $expectedValidation)
    {
        $elements = [
            'field' => [
                'visible' => true,
                'formElement' => 'input',
                'label' => __('City'),
                'value' => null,
                'sortOrder' => 1,
                'validation' => [
                    'input_validation' => $validationRule,
                ],
            ]
        ];

        $actualResult = $this->attributeMerger->merge(
            $elements,
            'provider',
            'dataScope',
            [
                'field' =>
                    [
                        'validation' => ['length' => true],
                    ],
            ]
        );

        $expectedResult = [
            $expectedValidation => true,
            'length' => true,
        ];

        $this->assertEquals($expectedResult, $actualResult['field']['validation']);
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
            ['length', 'validate-length'],
        ];
    }
}
