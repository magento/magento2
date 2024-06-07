<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Url;

use Magento\Framework\View\Url\CssResolver;
use PHPUnit\Framework\TestCase;

class CssResolverTest extends TestCase
{
    /**
     * @var CssResolver
     */
    protected $object;

    protected function setUp(): void
    {
        $this->object = new CssResolver();
    }

    public function testRelocateRelativeUrls()
    {
        $relatedPath = '/some/directory/two/another/file.ext';
        $filePath = '/some/directory/one/file.ext';

        $fixturePath = __DIR__ . '/_files/';
        $source = file_get_contents($fixturePath . 'source.css');
        $result = file_get_contents($fixturePath . 'resultNormalized.css');

        $this->assertEquals($result, $this->object->relocateRelativeUrls($source, $relatedPath, $filePath));
    }

    /**
     * @param string $cssContent
     * @param string $expectedResult
     * @dataProvider aggregateImportDirectivesDataProvider
     */
    public function testAggregateImportDirectives($cssContent, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->object->aggregateImportDirectives($cssContent));
    }

    /**
     * @return array
     */
    public static function aggregateImportDirectivesDataProvider()
    {
        $fixturePath = __DIR__ . '/_files/';
        $source = file_get_contents($fixturePath . 'sourceImport.css');
        $result = file_get_contents($fixturePath . 'resultImport.css');
        $sourceNoImport = 'li {background: url("https://example.com/absolute.gif");}';

        return [
            'empty' => ['', ''],
            'data without patterns' => [$sourceNoImport, $sourceNoImport],
            'data with patterns' => [$source, $result]
        ];
    }

    /**
     * @param string $cssContent
     * @param callback $inlineCallback
     * @param string $expectedResult
     * @dataProvider replaceRelativeUrlsDataProvider
     */
    public function testReplaceRelativeUrls($cssContent, $inlineCallback, $expectedResult)
    {
        $actual = $this->object->replaceRelativeUrls($cssContent, $inlineCallback);
        $this->assertEquals($expectedResult, $actual);
    }

    /**
     * @return array
     */
    public static function replaceRelativeUrlsDataProvider()
    {
        $fixturePath = __DIR__ . '/_files/';
        $callback = '\Magento\Framework\View\Test\Unit\Url\CssResolverTest::replaceRelativeUrl';
        $source = file_get_contents($fixturePath . 'source.css');
        $result = file_get_contents($fixturePath . 'result.css');
        $sourceNoPatterns = 'li {background: url("https://example.com/absolute.gif");}';

        return [
            'empty' => ['', '\Magento\Framework\View\Test\Unit\Url\CssResolverTest::doNothing', ''],
            'data without patterns' => [$sourceNoPatterns, $callback, $sourceNoPatterns],
            'data with patterns' => [$source, $callback, $result]
        ];
    }

    /**
     * A callback for testing replacing relative URLs
     *
     * @param string $relativeUrl
     * @return string
     */
    public static function replaceRelativeUrl($relativeUrl)
    {
        return '../two/another/' . $relativeUrl;
    }

    /**
     * A dummy callback for testing replacing relative URLs
     */
    public static function doNothing()
    {
        // do nothing
    }
}
