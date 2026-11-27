<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Email\Model;

use Magento\Config\Model\Config\TypePool;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * SMTP settings are environment specific, so app:config:dump writes them to env.php.
 */
class SmtpConfigTypeTest extends TestCase
{
    /**
     * @param string $path
     * @return void
     */
    #[DataProvider('environmentPathsDataProvider')]
    public function testSmtpPathIsEnvironment(string $path): void
    {
        $typePool = Bootstrap::getObjectManager()->get(TypePool::class);

        $this->assertTrue($typePool->isPresent($path, TypePool::TYPE_ENVIRONMENT));
    }

    /**
     * @return array
     */
    public static function environmentPathsDataProvider(): array
    {
        return [
            ['system/smtp/host'],
            ['system/smtp/port'],
            ['system/smtp/disable'],
        ];
    }
}
