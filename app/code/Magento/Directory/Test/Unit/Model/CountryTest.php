<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\Country;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    protected $country;

    /**
     * @var ListsInterface|MockObject
     */
    protected $localeListsMock;

    protected function setUp(): void
    {
        $this->localeListsMock = $this->getMockForAbstractClass(ListsInterface::class);

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
}
