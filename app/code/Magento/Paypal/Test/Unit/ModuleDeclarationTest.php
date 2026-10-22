<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit;

use PHPUnit\Framework\TestCase;

class ModuleDeclarationTest extends TestCase
{
    public function testSequenceContainsModuleUsedByPaypalConfig(): void
    {
        $module = new \DOMDocument();
        $module->load(dirname(__DIR__, 2) . '/etc/module.xml');

        $sequence = [];
        foreach ($module->getElementsByTagName('sequence')->item(0)->getElementsByTagName('module') as $node) {
            $sequence[] = $node->getAttribute('name');
        }

        $this->assertContains('Magento_Csp', $sequence);
    }
}
