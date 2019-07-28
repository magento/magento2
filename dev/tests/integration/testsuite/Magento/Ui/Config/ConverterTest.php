<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Config\FileIterator;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\ReadFactory;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var string
     */
    private $fixturePath;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->converter = $objectManager->create(Converter::class);
        $this->fixturePath = realpath(__DIR__ . '/../_files/view/ui_component');
    }

    /**
     * @param string $componentName
     * @return void
     * @dataProvider getComponentNameDataProvider
     */
    public function testConvert($componentName)
    {
        $expectedResult = $this->getExpectedResult($componentName);

        $fixtureFiles = $this->getFixtureFiles($componentName);
        foreach ($fixtureFiles as $filePath => $fileContent) {
            $dom = new \DOMDocument();
            $dom->loadXML($fileContent);
            $actualResult = $this->converter->convert($dom);

            if (isset($actualResult[Converter::DATA_ATTRIBUTES_KEY])) {
                unset($actualResult[Converter::DATA_ATTRIBUTES_KEY]);
            }

            $this->assertEquals(
                $expectedResult,
                $actualResult,
                "Wrong '{$this->getTypeByPath($filePath)}' configuration for '{$componentName}' Ui Component" . PHP_EOL
            );
        }
    }

    public function getComponentNameDataProvider()
    {
        return [
            ['action'],
            ['actionDelete'],
            ['actions'],
            ['actionsColumn'],
            ['bookmark'],
            ['boolean'],
            ['button'],
            ['checkbox'],
            ['checkboxset'],
            ['column'],
            ['columns'],
            ['columnsControls'],
            ['component'],
            ['dataSource'],
            ['date'],
            ['dynamicRows'],
            ['email'],
            ['exportButton'],
            ['field'],
            ['fieldset'],
            ['file'],
            ['fileUploader'],
            ['filterDate'],
            ['filterInput'],
            ['filterRange'],
            ['filters'],
            ['form'],
            ['hidden'],
            ['htmlContent'],
            ['imageUploader'],
            ['input'],
            ['insertForm'],
            ['insertListing'],
            ['listing'],
            ['listingToolbar'],
            ['massaction'],
            ['modal'],
            ['multiline'],
            ['multiselect'],
            ['paging'],
            ['radioset'],
            ['range'],
            ['select'],
            ['selectionsColumn'],
            ['tab'],
            ['text'],
            ['textarea'],
            ['wysiwyg'],
        ];
    }

    /**
     * Retrieve fixture files by $componentName
     *
     * @param string $componentName
     * @return FileIterator
     */
    private function getFixtureFiles($componentName)
    {
        $realPaths = [];
        foreach (['semantic', 'mixed', 'arbitrary'] as $filePath) {
            $realPaths[] = $this->fixturePath . '/' . $filePath . '/' . $componentName . '.xml';
        }
        return new FileIterator(new ReadFactory(new DriverPool), $realPaths);
    }

    /**
     * Retrieve expected result by $componentName
     *
     * @param string $componentName
     * @return array
     */
    private function getExpectedResult($componentName)
    {
        $filename = $this->fixturePath . '/expected/' . $componentName . '.php';
        if (is_file($filename)) {
            return include($filename);
        }

        return [];
    }

    /**
     * Retrieve fixture type by file path
     *
     * @param string $path
     * @return string
     */
    private function getTypeByPath($path)
    {
        $result = '';
        $pos = strpos($path, $this->fixturePath);
        if ($pos !== false) {
            $restParts = explode('/', substr($path, strlen($this->fixturePath) + 1));
            $result = array_shift($restParts);
        }

        return $result;
    }
}
