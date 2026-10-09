<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Model\Config\Processor;

use Magento\Store\Model\Config\PlaceholderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class PlaceholderTest extends TestCase
{
    public function testConfigPostProcessorKeepsRequestDependentPlaceholder(): void
    {
        $scopeConfig = [
            'web' => [
                'unsecure' => [
                    'base_url' => '{{base_url}}',
                    'base_link_url' => '{{unsecure_base_url}}',
                ],
                'secure' => [
                    'base_url' => '{{unsecure_base_url}}',
                    'base_link_url' => '{{secure_base_url}}',
                ],
            ],
        ];

        $result = Bootstrap::getObjectManager()->create(Placeholder::class)->process(
            ['default' => $scopeConfig, 'websites' => ['base' => $scopeConfig]]
        );

        foreach (['default' => $result['default'], 'websites/base' => $result['websites']['base']] as $scope => $data) {
            $this->assertSame('{{base_url}}', $data['web']['unsecure']['base_link_url'], $scope);
            $this->assertSame('{{base_url}}', $data['web']['secure']['base_link_url'], $scope);
        }
    }

    public function testReadTimePlaceholderResolvesBaseUrl(): void
    {
        $result = Bootstrap::getObjectManager()->get(PlaceholderInterface::class)->process(['url' => '{{base_url}}']);

        $this->assertStringNotContainsString('{{', $result['url']);
        $this->assertStringStartsWith('http', $result['url']);
    }
}
