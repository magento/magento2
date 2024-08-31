<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Validator\Street;
use Magento\Customer\Model\Customer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Customer street validator tests
 */
class StreetTest extends TestCase
{
    /**
     * @var Street
     */
    private Street $streetValidator;

    /**
     * @var Customer|MockObject
     */
    private MockObject $customerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        // PatternStreetValidator is not directly mocked anymore.
        $this->streetValidator = $this->createMock(Street::class);
        $this->customerMock = $this
            ->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getStreet'])
            ->getMock();
    }

    /**
     * Test for allowed characters in street addresses
     *
     * @param array $street
     * @param string $message
     * @return void
     * @dataProvider expectedPunctuationInStreetDataProvider
     */
    public function testValidateCorrectPunctuationInStreet(
        array $street,
        string $message
    ): void {
        $this->customerMock->expects($this->once())->method('getStreet')->willReturn($street);

        $isValid = $this->streetValidator->isValid($this->customerMock);
        $this->assertTrue($isValid, $message);
    }

    /**
     * Data provider for valid street names
     *
     * @return array
     */
    public function expectedPunctuationInStreetDataProvider(): array
    {
        return [
            [
                'street' => [
                    "123 Rue de l'Étoile",
                    "Ville d'Ölives, Çôte d'Azur",
                    "Çôte d'Azur"
                ],
                'message' => 'Unicode marks and Unicode letters must be allowed in street'
            ],
            [
                'street' => [
                    '876 Elm Way, Redwood Lodge',
                    '456 Pine Street, Serenity Cottage',
                    '321 Birch Boulevard, Willow Retreat'
                ],
                'message' => 'Comma must be allowed in street'
            ],
            [
                'street' => [
                    '321 Birch Boulevard-Retreat',
                    '234 Spruce Place-Residence',
                    '456 Pine Street-Haven'
                ],
                'message' => 'Hyphen must be allowed in street'
            ],
            [
                'street' => [
                    '1234 Elm St.',
                    'Main. Street',
                    '1234 Elm St'
                ],
                'message' => 'Period must be allowed in street'
            ],
            [
                'street' => [
                    'O\'Connell Street',
                    'O`Connell Street',
                    '321 Birch Boulevard ’Willow Retreat’'
                ],
                'message' => 'Quotes must be allowed in street'
            ],
            [
                'street' => [
                    '123 Main Street & Elm Avenue',
                    '456 Pine Street & Maple Avenue',
                    '789 Oak Lane & Cedar Road'
                ],
                'message' => 'Ampersand must be allowed in street'
            ],
            [
                'street' => [
                    'Oak Lane Space',
                    'Birch Boulevard Space',
                    'Spruce Place'
                ],
                'message' => 'Whitespace must be allowed in street'
            ],
            [
                'street' => [
                    '234 Spruce Place',
                    '321 Birch Boulevard',
                    '876 Elm Way'
                ],
                'message' => 'Digits must be allowed in street'
            ],
            [
                'street' => [
                    '1234 Elm St. [Apartment 5]',
                    'Main St. (Suite 200)',
                    '456 Pine St. [Unit 10]'
                ],
                'message' => 'Square brackets and parentheses must be allowed in street'
            ]
        ];
    }
}
