<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Entity;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class to test EAV Entity attribute model
 */
class AttributeTest extends TestCase
{
    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->attribute = $this->objectManager->get(Attribute::class);
        $this->localeResolver = $this->objectManager->get(ResolverInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->attribute = null;
        $this->objectManager = null;
        $this->localeResolver = null;

        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isStatic() && 0 !== strpos($property->getDeclaringClass()->getName(), 'PHPUnit')) {
                $property->setAccessible(true);
                $property->setValue($this, null);
            }
        }
    }

    /**
     * @param string $defaultValue
     * @param string $backendType
     * @param string $locale
     * @param string $expected
     * @dataProvider beforeSaveDataProvider
     * @throws
     */
    public function testBeforeSave(
        string $defaultValue,
        string $backendType,
        string $frontendInput,
        string $locale,
        string $expected
    ) {
        $this->attribute->setDefaultValue($defaultValue);
        $this->attribute->setBackendType($backendType);
        $this->attribute->setFrontendInput($frontendInput);
        $this->localeResolver->setLocale($locale);
        $this->attribute->beforeSave();

        $this->assertEquals($expected, $this->attribute->getDefaultValue());
    }

    /**
     * Data provider for beforeSaveData.
     *
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            ['21/07/18', 'datetime', 'date', 'en_AU', '2018-07-21 00:00:00'],
            ['07/21/18', 'datetime', 'date', 'en_US', '2018-07-21 00:00:00'],
            ['21/07/18', 'datetime', 'date', 'fr_FR', '2018-07-21 00:00:00'],
            ['21/07/18', 'datetime', 'date', 'de_DE', '2018-07-21 00:00:00'],
            ['21/07/18', 'datetime', 'date', 'uk_UA', '2018-07-21 00:00:00'],
            ['100.50', 'decimal', 'decimal', 'en_US', '100.50'],
            ['100,50', 'decimal', 'decimal', 'uk_UA', '100.5'],
            ['07/21/2019 2:30 PM', 'datetime', 'datetime', 'en_US', '2019-07-21 21:30:00'],
            ['21.07.2019 14:30', 'datetime', 'datetime', 'uk_UA', '2019-07-21 21:30:00'],
        ];
    }

    /**
     * @param string $defaultValue
     * @param string $backendType
     * @param string $locale
     * @param string $expected
     * @dataProvider beforeSaveErrorDataDataProvider
     */
    public function testBeforeSaveErrorData($defaultValue, $backendType, $locale, $expected)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->attribute->setDefaultValue($defaultValue);
        $this->attribute->setBackendType($backendType);
        $this->localeResolver->setLocale($locale);
        $this->attribute->beforeSave();

        $this->expectExceptionMessage($expected);
    }

    /**
     * Data provider for beforeSaveData with error result.
     *
     * @return array
     */
    public function beforeSaveErrorDataDataProvider()
    {
        return [
            'wrong date for Australia' => ['32/38', 'datetime', 'en_AU', 'Invalid default date'],
            'wrong date for States' => ['32/38', 'datetime', 'en_US', 'Invalid default date'],
            'wrong decimal separator for US' => ['100,50', 'decimal', 'en_US', 'Invalid default decimal value'],
            'wrong decimal separator for UA' => ['100.50', 'decimal', 'uk_UA', 'Invalid default decimal value'],
        ];
    }
}
