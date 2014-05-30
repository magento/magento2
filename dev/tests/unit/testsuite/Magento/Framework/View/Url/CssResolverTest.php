<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Url;

class CssResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Url\CssResolver
     */
    protected $object;

    protected function setUp()
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
    public function aggregateImportDirectivesDataProvider()
    {
        $fixturePath = __DIR__ . '/_files/';
        $source = file_get_contents($fixturePath . 'sourceImport.css');
        $result = file_get_contents($fixturePath . 'resultImport.css');
        $sourceNoImport = 'li {background: url("https://example.com/absolute.gif");}';

        return array(
            'empty' => array('', ''),
            'data without patterns' => array($sourceNoImport, $sourceNoImport),
            'data with patterns' => array($source, $result)
        );
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
        $callback = '\Magento\Framework\View\Url\CssResolverTest::replaceRelativeUrl';
        $source = file_get_contents($fixturePath . 'source.css');
        $result = file_get_contents($fixturePath . 'result.css');
        $sourceNoPatterns = 'li {background: url("https://example.com/absolute.gif");}';

        return array(
            'empty' => array('', '\Magento\Framework\View\Url\CssResolverTest::doNothing', ''),
            'data without patterns' => array($sourceNoPatterns, $callback, $sourceNoPatterns),
            'data with patterns' => array($source, $callback, $result)
        );
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
