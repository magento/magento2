<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Locale\Test\Unit\Bundle;

use Magento\Framework\Locale\Bundle\DataBundle;

class DataBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Locale\Bundle\DataBundle
     */
    protected $bundleObject;

    public function setUp()
    {
        $this->bundleObject = new DataBundle();
    }

    /**
     * @param string $locale
     * @param \ResourceBundle $result
     * #@dataProvider dataProviderGet
     */
    public function testGet($locale, \ResourceBundle $result)
    {
        $bundle = $this->bundleObject->get($locale);
        $this->assertInstanceOf('\ResourceBundle', $bundle);
        $this->assertEquals($result, $bundle);
        $this->assertEquals($result->count(), $bundle->count());
        $this->assertTrue($this->compareBundlesRecursively($bundle, $result));
        // Check the caching is working
        $bundleAgain = $this->bundleObject->get($locale);
        $this->assertInstanceOf('\ResourceBundle', $bundleAgain);
        $this->assertSame($bundle, $bundleAgain);
    }

    /**
     * Checks whether bundles contains the same data
     *
     * @param \ResourceBundle $first
     * @param \ResourceBundle $second
     * @return bool
     */
    protected function compareBundlesRecursively(\ResourceBundle $first, \ResourceBundle $second)
    {
        $isEquals = true;
        foreach ($first as $key => $value) {
            if ($value instanceof \ResourceBundle && $second[$key] instanceof \ResourceBundle) {
                $isEquals = $isEquals && $this->compareBundlesRecursively($value, $second[$key]);
            } else {
                $isEquals = $isEquals && $value === $second[$key];
            }
        }
        return $isEquals;
    }

    /**
     * @return array
     */
    public function dataProviderGet()
    {
        return [
            ['en', new \ResourceBundle('en', 'ICUDATA')],
            ['en_US', new \ResourceBundle('en', 'ICUDATA')],
            ['en_US_Variant', new \ResourceBundle('en', 'ICUDATA')],
            ['sr_Latn_SR', new \ResourceBundle('sr_Latn', 'ICUDATA')],
            ['sr_Cyrl_SR', new \ResourceBundle('sr_Cyrl', 'ICUDATA')],
        ];
    }
}
