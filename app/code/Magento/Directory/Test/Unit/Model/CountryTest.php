<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\Country;

class CountryTest extends \PHPUnit_Framework_TestCase
{
    protected $country;

    /**
     * @var \Magento\Framework\Locale\ListsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeListsMock;

    protected function setUp()
    {
        $this->localeListsMock = $this->getMock('Magento\Framework\Locale\ListsInterface');

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->country = $objectManager->getObject('Magento\Directory\Model\Country', [
            'localeLists' => $this->localeListsMock,
        ]);
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
