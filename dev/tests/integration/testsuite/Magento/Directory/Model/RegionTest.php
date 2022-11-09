<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Model;

use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Directory\Setup\Patch\Data\UpdateRegionNamesForSwitzerland as SwitzerlandRegionData;
use Magento\Framework\AppInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;

class RegionTest extends TestCase
{
    /**
     * @var Country
     */
    private $country;

    /**
     * @var RegionCollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->country = Bootstrap::getObjectManager()->create(Country::class);
        $this->regionCollectionFactory = Bootstrap::getObjectManager()->create(RegionCollectionFactory::class);
    }

    /**
     * Verify country has regions.
     *
     * @param string $countryId
     * @dataProvider getCountryIdDataProvider
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws LocalizedException
     */
    public function testCountryHasRegions(string $countryId): void
    {
        $country = $this->country->loadByCode($countryId);
        $region = $country->getRegions()->getItems();

        $this->assertNotEmpty($region, 'Country ' . $countryId . ' not have regions');
    }

    /**
     * Data provider for testCountryHasRegions
     *
     * @return array
     */
    public function getCountryIdDataProvider(): array
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
            ['countryId' => 'AL'],
            ['countryId' => 'BY'],
        ];
    }

    /**
     * Verify updated Switzerland regions
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testUpdatedSwitzerlandRegions(): void
    {
        $regionCollection = $this->regionCollectionFactory->create();
        $regionCollection->addCountryFilter(SwitzerlandRegionData::SWITZERLAND_COUNTRY_CODE);
        $regionCollection->addRegionCodeFilter(
            array_keys(SwitzerlandRegionData::SWITZERLAND_COUNTRY_REGION_DATA_TO_UPDATE)
        );
        $regionCollection->addBindParam(':region_locale', AppInterface::DISTRO_LOCALE_CODE);
        foreach ($regionCollection->getItems() as $regionItem) {
            $code = $regionItem->getData('code');
            $expectRegionName = SwitzerlandRegionData::SWITZERLAND_COUNTRY_REGION_DATA_TO_UPDATE[$code] ?? null;
            $this->assertEquals($expectRegionName, $regionItem->getData('default_name'));
            $this->assertEquals($expectRegionName, $regionItem->getData('name'));
        }
    }
}
