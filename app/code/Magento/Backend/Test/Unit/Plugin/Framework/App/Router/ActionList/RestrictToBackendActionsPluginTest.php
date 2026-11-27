<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Plugin\Framework\App\Router\ActionList;

use Magento\Backend\Controller\Adminhtml\Denied;
use Magento\Backend\Plugin\Framework\App\Router\ActionList\RestrictToBackendActionsPlugin;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\Router\ActionList;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RestrictToBackendActionsPluginTest extends TestCase
{
    /**
     * @param string[] $allowedActions
     * @param string|null $result
     * @param string|null $area
     * @param string|null $expected
     * @return void
     */
    #[DataProvider('afterGetDataProvider')]
    public function testAfterGet(array $allowedActions, ?string $result, ?string $area, ?string $expected): void
    {
        $plugin = new RestrictToBackendActionsPlugin($allowedActions);

        $this->assertSame(
            $expected,
            $plugin->afterGet($this->createMock(ActionList::class), $result, 'Magento_Backend', $area)
        );
    }

    /**
     * @return array
     */
    public static function afterGetDataProvider(): array
    {
        return [
            'backend action in admin area' => [[], Denied::class, 'adminhtml', Denied::class],
            'non-backend action in admin area' => [[], Forward::class, 'adminhtml', null],
            'allowed non-backend action in admin area' => [
                ['forward' => Forward::class],
                Forward::class,
                'adminhtml',
                Forward::class,
            ],
            'allowed non-backend action matched case-insensitively' => [
                ['forward' => strtoupper(Forward::class)],
                Forward::class,
                'adminhtml',
                Forward::class,
            ],
            'no action in admin area' => [[], null, 'adminhtml', null],
            'non-backend action in storefront' => [[], Forward::class, null, Forward::class],
        ];
    }
}
