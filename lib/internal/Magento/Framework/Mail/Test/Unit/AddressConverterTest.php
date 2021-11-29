<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail\Test\Unit;

use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\AddressFactory;
use Magento\Framework\Mail\Address;
use PHPUnit\Framework\TestCase;

class AddressConverterTest extends TestCase
{
    /**
     * @var Address
     */
    private $addressMock;

    /**
     * @var AddressFactory
     */
    private $addressFactoryMock;

    /**
     * @var AddressConverter
     */
    private $addressConverter;

    protected function setUp(): void
    {
        $this->addressMock = $this->createMock(Address::class);
        $this->addressFactoryMock = $this->createMock(AddressFactory::class);
        $this->addressConverter = new AddressConverter($this->addressFactoryMock);
    }

    /**
     * @param string $email
     * @param string $name
     * @param string $emailExpected
     * @param string $nameExpected
     * @dataProvider convertDataProvider
     */
    public function testConvert(string $email, string $name, string $emailExpected, string $nameExpected)
    {
        $this->addressFactoryMock->expects($this->once())
            ->method('create')
            ->with(['name' => $nameExpected, 'email' => $emailExpected])
            ->willReturn($this->addressMock);
        $address = $this->addressConverter->convert($email, $name);
        $this->assertInstanceOf(Address::class, $address);
    }

    /**
     * @return array
     */
    public function convertDataProvider(): array
    {
        return [
            [
                'email' => 'test@example.com',
                'name' => 'Test',
                'emailExpected' => 'test@example.com',
                'nameExpected' => 'Test'
            ],
            [
                'email' => 'tést@example.com',
                'name' => 'Test',
                'emailExpected' => 'xn--tst-bma@example.com',
                'nameExpected' => 'Test'
            ]
        ];
    }
}
