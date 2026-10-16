<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field\FieldArray;

use Magento\Framework\Component\ComponentRegistrar;
use PHPUnit\Framework\TestCase;

/**
 * Locks the FieldArray template contract used by form dependence:
 * the __empty sentinel must opt into disable/enable via the "disableable" class.
 */
class ArrayTemplateTest extends TestCase
{
    /**
     * @var string
     */
    private $templatePath;

    protected function setUp(): void
    {
        $modulePath = (new ComponentRegistrar())->getPath(ComponentRegistrar::MODULE, 'Magento_Config');
        $this->templatePath = $modulePath
            . '/view/adminhtml/templates/system/config/form/field/array.phtml';
    }

    public function testTemplateFileExists(): void
    {
        $this->assertFileExists($this->templatePath);
    }

    public function testEmptySentinelInputIsHiddenAndDisableable(): void
    {
        $html = file_get_contents($this->templatePath);
        $this->assertNotFalse($html);

        // Match the fixed attribute order used by the FieldArray template.
        $this->assertMatchesRegularExpression(
            '/type="hidden"\s+class="disableable"\s+name="/s',
            $html,
            'FieldArray __empty sentinel must be type=hidden with class disableable'
        );
        $this->assertStringContainsString('[__empty]', $html);
    }

    public function testEmptySentinelIsOutsideTableWrapperForLegacyLayout(): void
    {
        $html = file_get_contents($this->templatePath);
        $this->assertNotFalse($html);

        // Sentinel is after the closing of admin__control-table-wrapper (sibling of the table)
        $wrapperClose = strpos($html, 'admin__control-table-wrapper');
        $emptyPos = strpos($html, '[__empty]');
        $this->assertNotFalse($wrapperClose);
        $this->assertNotFalse($emptyPos);
        $this->assertGreaterThan(
            $wrapperClose,
            $emptyPos,
            '__empty must remain outside the table wrapper so dependence collection covers it via td.value'
        );
    }
}
