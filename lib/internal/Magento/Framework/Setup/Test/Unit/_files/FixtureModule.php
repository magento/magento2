<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Mvc\Fixture;

class Module
{
    public function getConfig(): array
    {
        return [
            'service_manager' => [
                'services' => [
                    'foo' => 'bar',
                ],
            ],
        ];
    }
}
