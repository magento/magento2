<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Validator\Street;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Address;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Customer street validator tests
 */
class StreetTest extends TestCase
{
    use MockCreationTrait;

    /**
     * Maximum allowed length per street line.
     */
    private const MAX_STREET_LENGTH = 255;

    /**
     * @var Street
     */
    private Street $nameValidator;

    /**
     * @var Customer|MockObject
     */
    private MockObject $customerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->nameValidator = new Street;
        $this->customerMock = $this->createPartialMockWithReflection(
            Customer::class,
            ['getStreet']
        );
    }

    /**
     * Test for allowed apostrophe and other punctuation characters in customer names
     *
     * @param array $street
     * @param string $message
     * @return void */
    #[DataProvider('expectedPunctuationInNamesDataProvider')]
    public function testValidateCorrectPunctuationInNames(
        array $street,
        string $message
    ) {
        $this->customerMock->expects($this->once())->method('getStreet')->willReturn($street);

        $isValid = $this->nameValidator->isValid($this->customerMock);
        $this->assertTrue($isValid, $message);
    }

    /**
     * @return array
     */
    public static function expectedPunctuationInNamesDataProvider(): array
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
                    "\u{2018}O’Connell Street\u{2019}",
                    '321 Birch Boulevard ‘Willow Retreat’'
                ],
                'message' => 'quotes must be allowed in street'
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
            ]
        ];
    }

    public function testStreetLineUpTo255CharactersIsValid(): void
    {
        $addressMock = $this->createPartialMockWithReflection(Address::class, ['getStreet']);
        $addressMock->expects($this->once())
            ->method('getStreet')
            ->willReturn([str_repeat('A', self::MAX_STREET_LENGTH)]);

        $this->assertTrue($this->nameValidator->isValid($addressMock));
    }

    public function testStreetLineExceeding255CharactersIsRejected(): void
    {
        $addressMock = $this->createPartialMockWithReflection(Address::class, ['getStreet']);
        $addressMock->expects($this->once())
            ->method('getStreet')
            ->willReturn([str_repeat('A', self::MAX_STREET_LENGTH + 1)]);

        $isValid = $this->nameValidator->isValid($addressMock);

        $this->assertFalse($isValid);
        $messages = $this->nameValidator->getMessages();
        $this->assertStringContainsString(
            'street address is too long',
            (string)($messages[0]['street'] ?? '')
        );
    }

    public function testStreetWithCommonSpecialCharactersIsAccepted(): void
    {
        $addressMock = $this->createPartialMockWithReflection(Address::class, ['getStreet']);
        $addressMock->expects($this->once())
            ->method('getStreet')
            ->willReturn(['123 Main St #4B, Apt (2B) & 1/2 Oak Lane']);

        $this->assertTrue($this->nameValidator->isValid($addressMock));
    }

    public function testStreetWithAngleBracketsIsRejected(): void
    {
        $addressMock = $this->createPartialMockWithReflection(Address::class, ['getStreet']);
        $addressMock->expects($this->once())
            ->method('getStreet')
            ->willReturn(['123 Main St <invalid>']);

        $isValid = $this->nameValidator->isValid($addressMock);

        $this->assertFalse($isValid);
        $messages = $this->nameValidator->getMessages();
        $this->assertStringContainsString(
            'Invalid Street Address',
            (string)($messages[0]['street'] ?? '')
        );
    }

    public function testTwoStreetLinesCombinedExceeding255CharactersIsRejected(): void
    {
        $addressMock = $this->createPartialMockWithReflection(Address::class, ['getStreet']);
        $addressMock->expects($this->once())
            ->method('getStreet')
            ->willReturn([str_repeat('A', 200), str_repeat('B', 60)]);

        $isValid = $this->nameValidator->isValid($addressMock);

        $this->assertFalse($isValid);
        $messages = $this->nameValidator->getMessages();
        $this->assertStringContainsString(
            'street address is too long',
            (string)($messages[0]['street'] ?? '')
        );
    }

    public function testJsonEncodedStreetIsAccepted(): void
    {
        $addressMock = $this->createPartialMockWithReflection(Address::class, ['getStreet']);
        $addressMock->expects($this->once())
            ->method('getStreet')
            ->willReturn(['["7700 W Parmer Ln","Bld D"]']);

        $this->assertTrue($this->nameValidator->isValid($addressMock));
    }
}
