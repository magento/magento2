<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\Country;
use Magento\Directory\Model\Country\FormatFactory;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    /**
     * @var Country
     */
    protected $country;

    /**
     * @var ListsInterface|MockObject
     */
    protected $localeListsMock;

    protected function setUp(): void
    {
        $this->localeListsMock = $this->createMock(ListsInterface::class);

        $objectManager = new ObjectManager($this);
        $this->country = $objectManager->getObject(
            Country::class,
            ['localeLists' => $this->localeListsMock]
        );
    }

    public function testGetName()
    {
        $this->localeListsMock->expects($this->once())
            ->method('getCountryTranslation')
            ->with(1, null)
            ->willReturn('United States');

        $this->country->setId(1);
        $this->assertEquals('United States', $this->country->getName());
    }

    public function testGetNameWithLocale()
    {
        $this->localeListsMock->expects($this->once())
            ->method('getCountryTranslation')
            ->with(1, 'de_DE')
            ->willReturn('Vereinigte Staaten');

        $this->country->setId(1);
        $this->assertEquals('Vereinigte Staaten', $this->country->getName('de_DE'));
    }

    public function testGetFormatsWithoutIdReturnsNullWithoutLoadingCollection()
    {
        $formatFactoryMock = $this->createMock(FormatFactory::class);
        $formatFactoryMock->expects($this->never())
            ->method('create');

        $country = (new ObjectManager($this))->getObject(
            Country::class,
            [
                'localeLists' => $this->localeListsMock,
                'formatFactory' => $formatFactoryMock,
            ]
        );

        $deprecations = [];
        set_error_handler(
            static function (int $errno, string $errstr) use (&$deprecations): bool {
                if ($errno === E_DEPRECATED) {
                    $deprecations[] = $errstr;
                }
                return true;
            },
            E_DEPRECATED
        );

        try {
            $this->assertNull($country->getFormats());
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $deprecations, implode(PHP_EOL, $deprecations));
    }
}
