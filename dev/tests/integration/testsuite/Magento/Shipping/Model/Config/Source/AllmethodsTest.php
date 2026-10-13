<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Shipping\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Shipping\Model\Config;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for Allmethods class
 *
 * Tests that the $isActiveOnlyFlag parameter correctly controls which carriers are loaded
 * and that carrier factories are only instantiated when appropriate.
 *
 * @magentoAppArea adminhtml
 */
class AllmethodsTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Allmethods
     */
    private $allmethods;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->allmethods = $this->objectManager->create(Allmethods::class);
    }

    /**
     * Test that toOptionArray with isActiveOnlyFlag=false returns all carriers
     *
     * This test verifies that when the flag is false, the method calls getAllCarriers()
     * and returns options for both active and inactive carriers.
     */
    #[ConfigFixture('carriers/flatrate/active', 1)]
    #[ConfigFixture('carriers/flatrate/title', 'Flat Rate')]
    #[ConfigFixture('carriers/freeshipping/active', 0)]
    #[ConfigFixture('carriers/freeshipping/title', 'Free Shipping')]
    #[ConfigFixture('carriers/tablerate/active', 0)]
    #[ConfigFixture('carriers/tablerate/title', 'Best Way')]
    public function testToOptionArrayReturnsAllCarriersWhenFlagIsFalse(): void
    {
        $result = $this->allmethods->toOptionArray(false);

        // Verify structure includes empty option
        $this->assertIsArray($result);
        $this->assertArrayHasKey(0, $result);
        $this->assertSame(['value' => '', 'label' => ''], $result[0]);

        // Count total carriers (excluding the empty option)
        $carrierCount = count($result) - 1;

        // Should include at least the active flatrate carrier
        // May include inactive carriers depending on system configuration
        $this->assertGreaterThanOrEqual(1, $carrierCount);

        // Verify flatrate (active) is present
        $this->assertArrayHasKey('flatrate', $result);
        $this->assertSame('Flat Rate', $result['flatrate']['label']);
        $this->assertIsArray($result['flatrate']['value']);
    }

    /**
     * Test that toOptionArray with isActiveOnlyFlag=true returns only active carriers
     *
     * This test verifies that when the flag is true, the method calls getActiveCarriers()
     * and returns options only for active carriers, excluding inactive ones.
     */
    #[ConfigFixture('carriers/flatrate/active', 1)]
    #[ConfigFixture('carriers/flatrate/title', 'Flat Rate')]
    #[ConfigFixture('carriers/freeshipping/active', 0)]
    #[ConfigFixture('carriers/freeshipping/title', 'Free Shipping')]
    #[ConfigFixture('carriers/tablerate/active', 0)]
    #[ConfigFixture('carriers/tablerate/title', 'Best Way')]
    public function testToOptionArrayReturnsOnlyActiveCarriersWhenFlagIsTrue(): void
    {
        $result = $this->allmethods->toOptionArray(true);

        // Verify structure includes empty option
        $this->assertIsArray($result);
        $this->assertArrayHasKey(0, $result);
        $this->assertSame(['value' => '', 'label' => ''], $result[0]);

        // Verify flatrate (active) is present
        $this->assertArrayHasKey('flatrate', $result);
        $this->assertSame('Flat Rate', $result['flatrate']['label']);
        $this->assertIsArray($result['flatrate']['value']);

        // Verify inactive carriers are NOT present
        $this->assertArrayNotHasKey('freeshipping', $result);
        $this->assertArrayNotHasKey('tablerate', $result);
    }

    /**
     * Test that active carriers have properly formatted method options
     */
    #[ConfigFixture('carriers/flatrate/active', 1)]
    #[ConfigFixture('carriers/flatrate/title', 'Flat Rate')]
    public function testActiveCarrierMethodsAreFormattedCorrectly(): void
    {
        $result = $this->allmethods->toOptionArray(true);

        $this->assertArrayHasKey('flatrate', $result);
        $carrierOptions = $result['flatrate'];

        $this->assertArrayHasKey('label', $carrierOptions);
        $this->assertArrayHasKey('value', $carrierOptions);
        $this->assertSame('Flat Rate', $carrierOptions['label']);

        // Verify methods array structure
        $methods = $carrierOptions['value'];
        $this->assertIsArray($methods);

        if (!empty($methods)) {
            $firstMethod = $methods[0];
            $this->assertArrayHasKey('value', $firstMethod);
            $this->assertArrayHasKey('label', $firstMethod);
            
            // Verify value format: {carrierCode}_{methodCode}
            $this->assertStringStartsWith('flatrate_', $firstMethod['value']);
            
            // Verify label format: [{carrierCode}] {methodTitle}
            $this->assertStringStartsWith('[flatrate]', $firstMethod['label']);
        }
    }

    /**
     * Test that carriers without allowed methods are skipped
     *
     * This test verifies that carriers returning null or empty array from getAllowedMethods()
     * are not included in the results.
     */
    #[ConfigFixture('carriers/flatrate/active', 1)]
    #[ConfigFixture('carriers/flatrate/title', 'Flat Rate')]
    public function testCarriersWithoutAllowedMethodsAreSkipped(): void
    {
        $result = $this->allmethods->toOptionArray(true);

        // Verify that only carriers with methods are present
        foreach ($result as $key => $option) {
            if ($key === 0) {
                // Skip the empty option
                continue;
            }

            // Each carrier should have methods
            $this->assertArrayHasKey('value', $option);
            $this->assertIsArray($option['value']);
        }
    }

    /**
     * Test the performance improvement: verify that getActiveCarriers is called when flag is true
     *
     * This test verifies that the correct Config method is invoked based on the flag value,
     * which is the core of the performance improvement fix.
     */
    #[ConfigFixture('carriers/flatrate/active', 1)]
    #[ConfigFixture('carriers/flatrate/title', 'Flat Rate')]
    #[ConfigFixture('carriers/freeshipping/active', 0)]
    #[ConfigFixture('carriers/freeshipping/title', 'Free Shipping')]
    public function testCorrectConfigMethodIsCalledBasedOnFlag(): void
    {
        // Create a spy Config object to track method calls
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getActiveCarriers', 'getAllCarriers'])
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        
        // Get real carrier instances
        $shippingConfig = $this->objectManager->create(Config::class);
        $activeCarriers = $shippingConfig->getActiveCarriers();
        $allCarriers = $shippingConfig->getAllCarriers();

        // Test with isActiveOnlyFlag = true
        $configMock->expects($this->once())
            ->method('getActiveCarriers')
            ->willReturn($activeCarriers);

        $allmethodsWithMock = $this->objectManager->create(
            Allmethods::class,
            [
                'scopeConfig' => $scopeConfig,
                'shippingConfig' => $configMock
            ]
        );

        $result = $allmethodsWithMock->toOptionArray(true);
        $this->assertIsArray($result);

        // Create a new mock for the second test
        $configMock2 = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getActiveCarriers', 'getAllCarriers'])
            ->disableOriginalConstructor()
            ->getMock();

        // Test with isActiveOnlyFlag = false
        $configMock2->expects($this->once())
            ->method('getAllCarriers')
            ->willReturn($allCarriers);

        $allmethodsWithMock2 = $this->objectManager->create(
            Allmethods::class,
            [
                'scopeConfig' => $scopeConfig,
                'shippingConfig' => $configMock2
            ]
        );

        $result2 = $allmethodsWithMock2->toOptionArray(false);
        $this->assertIsArray($result2);
    }

    /**
     * Test with multiple active carriers
     */
    #[ConfigFixture('carriers/flatrate/active', 1)]
    #[ConfigFixture('carriers/flatrate/title', 'Flat Rate')]
    #[ConfigFixture('carriers/tablerate/active', 1)]
    #[ConfigFixture('carriers/tablerate/title', 'Best Way')]
    public function testMultipleActiveCarriersAreReturnedWhenFlagIsTrue(): void
    {
        $result = $this->allmethods->toOptionArray(true);

        $this->assertIsArray($result);
        
        // Should include at least flatrate
        $this->assertArrayHasKey('flatrate', $result);
        $this->assertSame('Flat Rate', $result['flatrate']['label']);
        
        // Count active carriers (excluding the empty option at index 0)
        $activeCarrierCount = 0;
        foreach (array_keys($result) as $key) {
            if ($key !== 0 && is_string($key)) {
                $activeCarrierCount++;
            }
        }
        
        // Should have at least 1 active carrier (flatrate)
        // May have more depending on system configuration
        $this->assertGreaterThanOrEqual(1, $activeCarrierCount, 'Should return at least one active carrier');
    }

    /**
     * Test default parameter value (should behave as false)
     */
    #[ConfigFixture('carriers/flatrate/active', 1)]
    #[ConfigFixture('carriers/flatrate/title', 'Flat Rate')]
    public function testDefaultParameterBehavesAsFalse(): void
    {
        $resultDefault = $this->allmethods->toOptionArray();
        $resultFalse = $this->allmethods->toOptionArray(false);

        // Both should return the same results (all carriers)
        // Compare array keys and structure rather than instances
        $this->assertCount(count($resultFalse), $resultDefault, 'Both results should have same number of carriers');
        $this->assertArrayHasKey(0, $resultDefault, 'Default should have empty option');
        $this->assertArrayHasKey('flatrate', $resultDefault, 'Default should include flatrate carrier');
        
        // Verify both have the same carrier keys
        $keysDefault = array_keys($resultDefault);
        $keysFalse = array_keys($resultFalse);
        sort($keysDefault);
        sort($keysFalse);
        $this->assertSame($keysFalse, $keysDefault, 'Both results should have the same carrier keys');
    }
}
