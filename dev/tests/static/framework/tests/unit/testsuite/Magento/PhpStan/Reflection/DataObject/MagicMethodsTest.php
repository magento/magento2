<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PhpStan\Reflection\DataObject;

use PHPStan\Rules\Methods\CallMethodsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class MagicMethodsTest extends RuleTestCase
{
    /**
     * @inheritdoc
     */
    protected function getRule(): Rule
    {
        return $this->getContainer()->getByType(CallMethodsRule::class);
    }

    /**
     * Return directory path of fixture directory.
     *
     * @return string
     */
    private static function getFixturesDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR;
    }

    /**
     * Add config files.
     *
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [self::getFixturesDir() . 'config.neon'];
    }

    /**
     * Test Data Object Reflection extension.
     */
    public function testExtension()
    {
        $this->analyse(
            [
                self::getFixturesDir() . 'ClassWithCorrectUsageOfDataObject.php',
                self::getFixturesDir() . 'ClassWithIncorrectUsageOfDataObject.php',
                self::getFixturesDir() . 'ClassWithSessionManagerUsage.php'
            ],
            [
                // phpcs:disable Generic.Files.LineLength.TooLong
                [
                    'Parameter #1 $index of method Magento\Framework\DataObject::getBaz() expects int|string, Magento\Framework\DataObject given.',
                    38
                ],
                [
                    'Method Magento\Framework\DataObject::unsFoo() invoked with 1 parameter, 0 required.',
                    39
                ],
                [
                    'Method Magento\Framework\DataObject::setBaz() invoked with 0 parameters, 1 required.',
                    40
                ],
                [
                    'Method Magento\Framework\DataObject::hasFoo() invoked with 1 parameter, 0 required.',
                    43
                ],
                [
                    'Method Magento\Framework\DataObject::setStuff() invoked with 0 parameters, 1 required.',
                    44
                ],
                [
                    'Parameter #1 $index of method Magento\Framework\DataObject::getSomething() expects int|string, bool given.',
                    47
                ],
                [
                    'Parameter #1 $index of method Magento\Framework\Session\SessionManager::getBaz() expects int|string, Magento\Framework\Session\SessionManager given.',
                    41
                ],
                [
                    'Method Magento\Framework\Session\SessionManager::unsFoo() invoked with 1 parameter, 0 required.',
                    42
                ],
                [
                    'Method Magento\Framework\Session\SessionManager::setBaz() invoked with 0 parameters, 1 required.',
                    43
                ],
                [
                    'Method Magento\Framework\Session\SessionManager::hasFoo() invoked with 1 parameter, 0 required.',
                    46
                ],
                [
                    'Method Magento\Framework\Session\SessionManager::setStuff() invoked with 0 parameters, 1 required.',
                    47
                ],
                [
                    'Parameter #1 $index of method Magento\Framework\Session\SessionManager::getSomething() expects int|string, bool given.',
                    50
                ]
                // phpcs:enable Generic.Files.LineLength.TooLong
            ]
        );
    }
}
