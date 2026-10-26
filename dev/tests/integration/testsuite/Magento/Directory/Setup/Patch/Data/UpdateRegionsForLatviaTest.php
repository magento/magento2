<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test Latvian regions after a clean install and after upgrading an installation with pre-reform regions.
 */
class UpdateRegionsForLatviaTest extends TestCase
{
    /**
     * Region codes valid after the 2021 reform and the 2025 Varakļāni merger.
     */
    private const array EXPECTED_CODES = [
        'LV-DGV', 'LV-JEL', 'LV-JUR', 'LV-LPX', 'LV-REZ', 'LV-RIX', 'LV-VEN',
        'LV-002', 'LV-007', 'LV-011', 'LV-015', 'LV-016', 'LV-022', 'LV-026',
        'LV-033', 'LV-041', 'LV-042', 'LV-047', 'LV-050', 'LV-052', 'LV-054',
        'LV-056', 'LV-058', 'LV-059', 'LV-062', 'LV-067', 'LV-068', 'LV-073',
        'LV-077', 'LV-080', 'LV-087', 'LV-088', 'LV-089', 'LV-091', 'LV-094',
        'LV-097', 'LV-099', 'LV-101', 'LV-106', 'LV-111', 'LV-112', 'LV-113',
    ];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $this->connection = $this->resourceConnection->getConnection();
    }

    /**
     * @return void
     */
    public function testCleanInstall(): void
    {
        $regions = $this->getLatvianRegions();
        $codes = array_column($regions, 'code');

        $this->assertSameSize(array_unique($codes), $codes, 'Latvian region codes must be unique.');
        $this->assertEqualsCanonicalizing(self::EXPECTED_CODES, $codes);
        foreach ($regions as $region) {
            $this->assertSame(
                $region['default_name'],
                $region['name'],
                sprintf('Region %s must have an en_US name equal to its default name.', $region['code'])
            );
        }
        $this->assertSame('Dienvidkurzemes novads', $this->getRegion('LV-112')['default_name']);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @return void
     */
    public function testUpgrade(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->create(DataInstaller::class)->addCountryRegions(
            $this->connection,
            [
                ['LV', 'Aglonas novads', 'Aglonas novads'],
                ['LV', 'LV-102', 'Varakļānu novads'],
                ['LV', 'LV-RI', 'Rīgas novads'],
                ['LV', 'Ērgļu novads', 'Ērgļu novads'],
                ['LV', 'LV-CUSTOM', 'Custom region'],
            ]
        );
        $this->setRegionName('LV-112', 'Dienvidkurzemes Novads');

        $aglonaId = $this->getRegionId('Aglonas novads');
        $varaklaniId = $this->getRegionId('LV-102');
        $rigaDistrictId = $this->getRegionId('LV-RI');
        $ergliId = $this->getRegionId('Ērgļu novads');

        $this->connection->update(
            $this->resourceConnection->getTableName('customer_address_entity'),
            ['country_id' => 'LV', 'region_id' => $aglonaId, 'region' => 'Aglonas novads'],
            ['entity_id = ?' => 1]
        );
        $orderId = (int)$objectManager->create(Order::class)->loadByIncrementId('100000001')->getId();
        $orderAddressTable = $this->resourceConnection->getTableName('sales_order_address');
        $this->connection->update(
            $orderAddressTable,
            ['country_id' => 'LV', 'region_id' => $varaklaniId, 'region' => 'Varakļānu novads'],
            ['parent_id = ?' => $orderId, 'address_type = ?' => 'billing']
        );
        $this->connection->update(
            $orderAddressTable,
            ['country_id' => 'LV', 'region_id' => $rigaDistrictId, 'region' => 'Rīgas novads'],
            ['parent_id = ?' => $orderId, 'address_type = ?' => 'shipping']
        );
        $this->connection->insert(
            $this->resourceConnection->getTableName('tax_calculation_rate'),
            [
                'tax_country_id' => 'LV',
                'tax_region_id' => $ergliId,
                'tax_postcode' => '*',
                'code' => 'LV-Erglu-test',
                'rate' => 21,
            ]
        );

        $objectManager->create(UpdateRegionsForLatvia::class)->apply();

        // Obsolete regions are removed together with their locale names.
        $this->assertNull($this->getRegion('Aglonas novads'));
        $this->assertNull($this->getRegion('LV-102'));
        $this->assertNull($this->getRegion('LV-RI'));
        $this->assertFalse(
            (bool)$this->connection->fetchOne(
                $this->connection->select()
                    ->from($this->resourceConnection->getTableName('directory_country_region_name'), ['region_id'])
                    ->where('region_id IN (?)', [$aglonaId, $varaklaniId, $rigaDistrictId])
            )
        );

        // Custom regions and regions used by tax rates are kept.
        $this->assertNotNull($this->getRegion('LV-CUSTOM'));
        $this->assertNotNull($this->getRegion('Ērgļu novads'));

        // Customer address is moved to the successor region.
        $customerAddress = $this->connection->fetchRow(
            $this->connection->select()
                ->from($this->resourceConnection->getTableName('customer_address_entity'), ['region_id', 'region'])
                ->where('entity_id = ?', 1)
        );
        $this->assertSame($this->getRegionId('LV-073'), (int)$customerAddress['region_id']);
        $this->assertSame('Preiļu novads', $customerAddress['region']);

        // Order addresses keep the historical region name.
        $orderAddresses = $this->connection->fetchAssoc(
            $this->connection->select()
                ->from($orderAddressTable, ['address_type', 'region_id', 'region'])
                ->where('parent_id = ?', $orderId)
        );
        $this->assertSame($this->getRegionId('LV-059'), (int)$orderAddresses['billing']['region_id']);
        $this->assertSame('Varakļānu novads', $orderAddresses['billing']['region']);
        $this->assertNull($orderAddresses['shipping']['region_id']);
        $this->assertSame('Rīgas novads', $orderAddresses['shipping']['region']);

        // Names are corrected in both region tables, and codes stay unique.
        $this->assertSame('Dienvidkurzemes novads', $this->getRegion('LV-112')['default_name']);
        $this->assertSame('Dienvidkurzemes novads', $this->getRegion('LV-112')['name']);
        $codes = array_column($this->getLatvianRegions(), 'code');
        $this->assertSameSize(array_unique($codes), $codes);
        $this->assertEqualsCanonicalizing(
            array_merge(self::EXPECTED_CODES, ['LV-CUSTOM', 'Ērgļu novads']),
            $codes
        );
    }

    /**
     * Get Latvian regions with their en_US names.
     *
     * @return array
     */
    private function getLatvianRegions(): array
    {
        return $this->connection->fetchAll(
            $this->connection->select()
                ->from(
                    ['region' => $this->resourceConnection->getTableName('directory_country_region')],
                    ['region_id', 'code', 'default_name']
                )
                ->joinLeft(
                    ['name' => $this->resourceConnection->getTableName('directory_country_region_name')],
                    'name.region_id = region.region_id AND name.locale = \'en_US\'',
                    ['name']
                )
                ->where('region.country_id = ?', 'LV')
        );
    }

    /**
     * Get Latvian region by code.
     *
     * @param string $code
     * @return array|null
     */
    private function getRegion(string $code): ?array
    {
        foreach ($this->getLatvianRegions() as $region) {
            if ($region['code'] === $code) {
                return $region;
            }
        }

        return null;
    }

    /**
     * Get Latvian region ID by code.
     *
     * @param string $code
     * @return int
     */
    private function getRegionId(string $code): int
    {
        return (int)$this->getRegion($code)['region_id'];
    }

    /**
     * Set default and en_US name of a Latvian region.
     *
     * @param string $code
     * @param string $name
     * @return void
     */
    private function setRegionName(string $code, string $name): void
    {
        $regionId = $this->getRegionId($code);
        $this->connection->update(
            $this->resourceConnection->getTableName('directory_country_region'),
            ['default_name' => $name],
            ['region_id = ?' => $regionId]
        );
        $this->connection->update(
            $this->resourceConnection->getTableName('directory_country_region_name'),
            ['name' => $name],
            ['region_id = ?' => $regionId, 'locale = ?' => 'en_US']
        );
    }
}
