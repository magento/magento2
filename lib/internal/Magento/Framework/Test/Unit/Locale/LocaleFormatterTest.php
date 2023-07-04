<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Locale;

use Magento\Framework\Locale\ResolverInterface as LocalResolverInterface;
use Magento\Framework\Locale\LocaleFormatter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test locale formatter view model
 */
class LocaleFormatterTest extends TestCase
{
    /**
     * @var LocaleFormatter
     */
    private $model;

    /**
     * @var LocalResolverInterface|MockObject
     */
    private $localeResolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->localeResolver = $this->getMockForAbstractClass(LocalResolverInterface::class);
        $this->model = new LocaleFormatter($this->localeResolver);
    }

    /**
     * Test locale is in the JS friendly format
     */
    public function testGetLocaleJs()
    {
        $this->localeResolver->expects(self::atLeastOnce())->method('getLocale')->willReturn('en_US');
        $this->assertEquals('en-US', $this->model->getLocaleJs());
    }

    /**
     * Test that numbers are represented with the respect to locale
     */
    public function testFormatNumber()
    {
        $this->localeResolver->expects(self::atLeastOnce())->method('getLocale')->willReturn('ar_SA');
        $this->assertEquals('١٠٠٠٠', $this->model->formatNumber(10000));
    }
}
