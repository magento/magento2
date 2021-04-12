<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail\Test\Unit;

use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\AddressFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\Config\Config;
use Magento\Framework\ObjectManager\Factory\Dynamic\Developer;
use Magento\Framework\ObjectManager\Relations\Runtime;
use PHPUnit\Framework\TestCase;

class AddressConverterTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $config = new Config(
            new Runtime()
        );
        $factory = new Developer(
            $config
        );
        $this->objectManager = new ObjectManager($factory, $config);
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
        $addressFactory = new AddressFactory($this->objectManager);
        $addressConverter = new AddressConverter($addressFactory);
        $address = $addressConverter->convert($email, $name);
        $this->assertSame($emailExpected, $address->getEmail());
        $this->assertSame($nameExpected, $address->getName());
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
