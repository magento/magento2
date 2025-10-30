<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Config\Source;

use Magento\NewRelicReporting\Model\Config\Source\ApiMode;
use Magento\Framework\Data\OptionSourceInterface;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ApiMode source model
 */
class ApiModeTest extends TestCase
{
    /**
     * @var ApiMode
     */
    private $apiMode;

    /**
     * Set up test dependencies
     */
    protected function setUp(): void
    {
        $this->apiMode = new ApiMode();
    }

    /**
     * Test that ApiMode implements OptionSourceInterface
     */
    public function testImplementsOptionSourceInterface(): void
    {
        $this->assertInstanceOf(OptionSourceInterface::class, $this->apiMode);
    }

    /**
     * Test toOptionArray method returns correct structure and values
     */
    public function testToOptionArrayReturnsCorrectStructure(): void
    {
        $result = $this->apiMode->toOptionArray();

        // Assert it's an array
        $this->assertIsArray($result);

        // Assert it has exactly 2 options
        $this->assertCount(2, $result);

        // Assert structure of first option (v2 REST)
        $this->assertArrayHasKey('value', $result[0]);
        $this->assertArrayHasKey('label', $result[0]);
        $this->assertEquals(ApiMode::MODE_V2_REST, $result[0]['value']);
        $this->assertEquals('v2 REST (Legacy)', $result[0]['label']->render());

        // Assert structure of second option (NerdGraph)
        $this->assertArrayHasKey('value', $result[1]);
        $this->assertArrayHasKey('label', $result[1]);
        $this->assertEquals(ApiMode::MODE_NERDGRAPH, $result[1]['value']);
        $this->assertEquals('NerdGraph (GraphQL) - Recommended', $result[1]['label']->render());
    }

    /**
     * Test toOptionArray method values match constants
     */
    public function testToOptionArrayUsesConstants(): void
    {
        $result = $this->apiMode->toOptionArray();

        $values = array_column($result, 'value');

        $this->assertContains(ApiMode::MODE_V2_REST, $values);
        $this->assertContains(ApiMode::MODE_NERDGRAPH, $values);
    }

    /**
     * Test toArray method returns correct key-value structure
     */
    public function testToArrayReturnsCorrectStructure(): void
    {
        $result = $this->apiMode->toArray();

        // Assert it's an array
        $this->assertIsArray($result);

        // Assert it has exactly 2 options
        $this->assertCount(2, $result);

        // Assert keys match constants
        $this->assertArrayHasKey(ApiMode::MODE_V2_REST, $result);
        $this->assertArrayHasKey(ApiMode::MODE_NERDGRAPH, $result);

        // Assert values are correct
        $this->assertEquals('v2 REST (Legacy)', $result[ApiMode::MODE_V2_REST]->render());
        $this->assertEquals('NerdGraph (GraphQL) - Recommended', $result[ApiMode::MODE_NERDGRAPH]->render());
    }

    /**
     * Test constants have expected values
     */
    public function testConstantsHaveExpectedValues(): void
    {
        $this->assertEquals('v2_rest', ApiMode::MODE_V2_REST);
        $this->assertEquals('nerdgraph', ApiMode::MODE_NERDGRAPH);
    }

    /**
     * Test both methods return same option count
     */
    public function testBothMethodsReturnSameOptionCount(): void
    {
        $optionArray = $this->apiMode->toOptionArray();
        $array = $this->apiMode->toArray();

        $this->assertCount(count($optionArray), $array);
    }

    /**
     * Test that all constants are used in both methods
     */
    public function testAllConstantsAreUsedInBothMethods(): void
    {
        $optionArray = $this->apiMode->toOptionArray();
        $array = $this->apiMode->toArray();

        // Get values from toOptionArray
        $optionValues = array_column($optionArray, 'value');

        // Get keys from toArray
        $arrayKeys = array_keys($array);

        // Both should contain the same constants
        $this->assertEqualsCanonicalizing($optionValues, $arrayKeys);

        // Verify specific constants are present
        $expectedConstants = [ApiMode::MODE_V2_REST, ApiMode::MODE_NERDGRAPH];

        foreach ($expectedConstants as $constant) {
            $this->assertContains($constant, $optionValues);
            $this->assertArrayHasKey($constant, $array);
        }
    }

    /**
     * Test labels consistency between methods
     */
    public function testLabelsConsistencyBetweenMethods(): void
    {
        $optionArray = $this->apiMode->toOptionArray();
        $array = $this->apiMode->toArray();

        // Create mapping from toOptionArray
        $optionLabels = [];
        foreach ($optionArray as $option) {
            $optionLabels[$option['value']] = $option['label']->render();
        }

        // Create mapping from toArray
        $arrayLabels = [];
        foreach ($array as $key => $label) {
            $arrayLabels[$key] = $label->render();
        }

        // Labels should be identical for same keys
        $this->assertEquals($optionLabels, $arrayLabels);
    }

    /**
     * Test method return types
     */
    public function testMethodReturnTypes(): void
    {
        $optionArray = $this->apiMode->toOptionArray();
        $array = $this->apiMode->toArray();

        // Both should return arrays
        $this->assertIsArray($optionArray);
        $this->assertIsArray($array);

        // toOptionArray should return array of arrays
        foreach ($optionArray as $option) {
            $this->assertIsArray($option);
        }

        // toArray should have string keys
        foreach (array_keys($array) as $key) {
            $this->assertIsString($key);
        }
    }

    /**
     * Test that methods are immutable (don't modify internal state)
     */
    public function testMethodsAreImmutable(): void
    {
        $firstCall = $this->apiMode->toOptionArray();
        $secondCall = $this->apiMode->toOptionArray();

        $this->assertEquals($firstCall, $secondCall);

        $firstArrayCall = $this->apiMode->toArray();
        $secondArrayCall = $this->apiMode->toArray();

        $this->assertEquals($firstArrayCall, $secondArrayCall);
    }

    /**
     * Test specific label content
     */
    public function testSpecificLabelContent(): void
    {
        $optionArray = $this->apiMode->toOptionArray();

        // Find v2 REST option
        $v2RestOption = null;
        $nerdGraphOption = null;

        foreach ($optionArray as $option) {
            if ($option['value'] === ApiMode::MODE_V2_REST) {
                $v2RestOption = $option;
            } elseif ($option['value'] === ApiMode::MODE_NERDGRAPH) {
                $nerdGraphOption = $option;
            }
        }

        $this->assertNotNull($v2RestOption);
        $this->assertNotNull($nerdGraphOption);

        // Test specific label content
        $this->assertStringContainsString('Legacy', $v2RestOption['label']->render());
        $this->assertStringContainsString('v2 REST', $v2RestOption['label']->render());

        $this->assertStringContainsString('Recommended', $nerdGraphOption['label']->render());
        $this->assertStringContainsString('NerdGraph', $nerdGraphOption['label']->render());
        $this->assertStringContainsString('GraphQL', $nerdGraphOption['label']->render());
    }
}
