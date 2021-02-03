<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Model;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class RegionTest extends TestCase
{
    /**
     * @var Country
     */
    protected $country;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->country = Bootstrap::getObjectManager()->create(Country::class);
    }

    /**
     * Verify country has regions.
     *
     * @var string $countryId
     * @dataProvider getCountryIdDataProvider
     */
    public function testCountryHasRegions($countryId)
    {
        $country = $this->country->loadByCode($countryId);
        $region = $country->getRegions()->getItems();

        $this->assertTrue(!empty($region), 'Country ' . $countryId . ' not have regions');
    }

    /**
     * Data provider for testCountryHasRegions
     *
     * @return array
     */
    public function getCountryIdDataProvider():array
    {
        return [
            ['countryId' => 'US'],
            ['countryId' => 'CA'],
            ['countryId' => 'CN'],
            ['countryId' => 'IN'],
            ['countryId' => 'AU'],
            ['countryId' => 'BE'],
            ['countryId' => 'CO'],
            ['countryId' => 'MX'],
            ['countryId' => 'PL'],
            ['countryId' => 'IT'],
            ['countryId' => 'BG'],
            ['countryId' => 'AR'],
            ['countryId' => 'BO'],
            ['countryId' => 'CL'],
            ['countryId' => 'EC'],
            ['countryId' => 'GY'],
            ['countryId' => 'PY'],
            ['countryId' => 'PE'],
            ['countryId' => 'SR'],
            ['countryId' => 'VE'],
            ['countryId' => 'PT'],
            ['countryId' => 'IS'],
            ['countryId' => 'SE'],
            ['countryId' => 'GR'],
            ['countryId' => 'DK'],
            ['countryId' => 'AL']
        ];
    }
}
