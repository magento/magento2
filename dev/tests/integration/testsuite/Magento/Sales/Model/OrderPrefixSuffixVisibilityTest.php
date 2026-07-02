<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

use Magento\Config\Model\Config\Source\Nooptreq;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests visibility of customer prefix/suffix via Order::getCustomerName(),
 * covering null (unset), empty string, optional and required config values.
 *
 * @magentoDbIsolation enabled
 */
class OrderPrefixSuffixVisibilityTest extends TestCase
{
    private const XML_PATH_PREFIX_SHOW = 'customer/address/prefix_show';
    private const XML_PATH_SUFFIX_SHOW = 'customer/address/suffix_show';

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $om;

    protected function setUp(): void
    {
        $this->om = Bootstrap::getObjectManager();
    }

    #[DataProvider('visibilityCases')]
    public function testPrefixVisibility(?string $value, bool $setValue, bool $expectedVisible): void
    {
        // Ensure suffix never interferes in this test
        $this->setConfig(self::XML_PATH_SUFFIX_SHOW, Nooptreq::VALUE_NO);

        if ($setValue) {
            $this->setConfig(self::XML_PATH_PREFIX_SHOW, $value);
        }
        // If not set, path remains unset (null), simulating the reported case.

        $order = $this->makeOrder('Dr', 'John', 'Doe', 'Jr'); // suffix ignored here
        $expected = $expectedVisible ? 'Dr John Doe' : 'John Doe';

        $this->assertSame($expected, $order->getCustomerName());
    }

    #[DataProvider('visibilityCases')]
    public function testSuffixVisibility(?string $value, bool $setValue, bool $expectedVisible): void
    {
        // Ensure prefix never interferes in this test
        $this->setConfig(self::XML_PATH_PREFIX_SHOW, Nooptreq::VALUE_NO);

        if ($setValue) {
            $this->setConfig(self::XML_PATH_SUFFIX_SHOW, $value);
        }
        // If not set, path remains unset (null), simulating the reported case.

        $order = $this->makeOrder('Dr', 'John', 'Doe', 'Jr'); // prefix ignored here
        $expected = $expectedVisible ? 'John Doe Jr' : 'John Doe';

        $this->assertSame($expected, $order->getCustomerName());
    }

    public static function visibilityCases(): array
    {
        return [
            // value, setValue, expectedVisible
            'unset_null_defaults_to_no' => [null, false, false], // path not set → null
            'explicit_empty_string_no'  => [Nooptreq::VALUE_NO, true, false], // ''
            'optional_visible'          => [Nooptreq::VALUE_OPTIONAL, true, true], // 'opt'
            'required_visible'          => [Nooptreq::VALUE_REQUIRED, true, true], // 'req'
        ];
    }

    private function makeOrder(string $prefix, string $first, string $last, string $suffix): Order
    {
        /** @var Order $order */
        $order = $this->om->create(Order::class);
        $order->setCustomerPrefix($prefix);
        $order->setCustomerFirstname($first);
        $order->setCustomerLastname($last);
        $order->setCustomerSuffix($suffix);
        // Middlename left null to avoid affecting formatting.
        return $order;
    }

    private function setConfig(
        string $path,
        ?string $value,
        string $scope = ScopeInterface::SCOPE_STORE,
        string $scopeCode = 'default'
    ): void {
        $this->om->get(MutableScopeConfigInterface::class)->setValue($path, $value, $scope, $scopeCode);
    }
}
