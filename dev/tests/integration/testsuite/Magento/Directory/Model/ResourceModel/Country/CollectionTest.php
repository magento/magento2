<?php

namespace Magento\Directory\Model\ResourceModel\Country;

use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Country Resource Collection Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    private $countryCollection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->countryCollection = Bootstrap::getObjectManager()->get(Collection::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
    }

    /**
     * @covers \Magento\Directory\Model\ResourceModel\Country\Collection::loadByStore
     *
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture default_store general/country/allow US
     * @magentoConfigFixture fixture_second_store_store general/country/allow DE
     */
    public function testLoadByStoreShouldResetPreviousStoreFilter()
    {
        $defaultStore = $this->storeManager->getDefaultStoreView();
        $secondStore = $this->storeManager->getStore('fixture_second_store');

        $this->assertEquals(['US'], $this->countryCollection->loadByStore($defaultStore)->getAllIds());
        $this->assertEquals(['DE'], $this->countryCollection->loadByStore($secondStore)->getAllIds());
    }
}
