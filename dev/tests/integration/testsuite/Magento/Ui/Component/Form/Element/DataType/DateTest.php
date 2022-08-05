<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Component\Form\Element\DataType;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for date component.
 */
class DateTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var DateFactory */
    private $dateFactory;

    /** @var ResolverInterface */
    private $localeResolver;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->dateFactory = $this->objectManager->get(DateFactory::class);
        $this->localeResolver = $this->objectManager->get(ResolverInterface::class);
    }

    /**
     * @dataProvider localeDataProvider
     *
     * @param string $locale
     * @param string $dateFormat
     * @return void
     */
    public function testDateFormat(string $locale, string $dateFormat): void
    {
        $this->localeResolver->setLocale($locale);
        $date = $this->dateFactory->create();
        $date->prepare();
        $this->assertEquals($dateFormat, $date->getData('config')['options']['dateFormat']);
    }

    /**
     * @return array
     */
    public function localeDataProvider(): array
    {
        return [
            ['en_GB', 'dd/MM/y'], ['en_US', 'M/d/yy'],
        ];
    }
}
