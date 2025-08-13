<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel\Address\Attribute\Source;

use Magento\Eav\Model\Config;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

class CountryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var UniversalFactory
     */
    private $factory;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->eavConfig = $this->objectManager->get(Config::class);
        $this->factory = $this->objectManager->get(UniversalFactory::class);

        parent::setUp();
    }

    /**
     * Assert that countries are returned according to allowed countries per respective website
     *
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     * @magentoConfigFixture default_store general/country/default NL
     * @magentoConfigFixture default_store general/country/allow NL
     * @magentoConfigFixture fixture_second_store_store general/country/default UA
     * @magentoConfigFixture fixture_second_store_store general/country/allow UA
     * @dataProvider dataProvider
     */
    public function testMethod($website, $expectedCountryCont, $expectedCountryCode)
    {
        $websiteId = $this->storeManager->getWebsite($website)->getId();
        $attribute = $this->eavConfig->getAttribute('customer_address', 'country_id');
        $attribute->setWebsite($websiteId);

        $countryOptions = $this->factory->create($attribute->getSourceModel())
            ->setAttribute($attribute)->getAllOptions();

        $countryOptions = array_map(fn($countryOption) => $countryOption['value'], $countryOptions);
        $this->assertEquals($expectedCountryCont, count($countryOptions));
        $this->assertContains($expectedCountryCode, $countryOptions);
    }

    /**
     * @return array
     */
    public static function dataProvider()
    {
        return [
            ['admin', 249, 'GB'],
            ['base', 1, 'NL'],
            ['test', 1, 'UA'],
        ];
    }
}
