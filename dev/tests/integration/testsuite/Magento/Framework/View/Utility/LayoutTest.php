<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Utility;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Utility\Layout
     */
    protected $_utility;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(
            [
                Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [
                    DirectoryList::APP => ['path' => BP . '/dev/tests/integration'],
                ],
            ]
        );
        $this->_utility = new \Magento\Framework\View\Utility\Layout($this);
    }

    /**
     * Assert that the actual layout update instance represents the expected layout update file
     *
     * @param string $expectedUpdateFile
     * @param \Magento\Framework\View\Layout\ProcessorInterface $actualUpdate
     */
    protected function _assertLayoutUpdate($expectedUpdateFile, $actualUpdate)
    {
        $this->assertInstanceOf('Magento\Framework\View\Layout\ProcessorInterface', $actualUpdate);

        $layoutUpdateXml = $actualUpdate->getFileLayoutUpdatesXml();
        $this->assertInstanceOf('Magento\Framework\View\Layout\Element', $layoutUpdateXml);
        $this->assertXmlStringEqualsXmlFile($expectedUpdateFile, $layoutUpdateXml->asNiceXml());
    }

    /**
     * @param string|array $inputFiles
     * @param string $expectedFile
     *
     * @dataProvider getLayoutFromFixtureDataProvider
     */
    public function testGetLayoutUpdateFromFixture($inputFiles, $expectedFile)
    {
        $layoutUpdate = $this->_utility->getLayoutUpdateFromFixture($inputFiles);
        $this->_assertLayoutUpdate($expectedFile, $layoutUpdate);
    }

    /**
     * @param string|array $inputFiles
     * @param string $expectedFile
     *
     * @dataProvider getLayoutFromFixtureDataProvider
     */
    public function testGetLayoutFromFixture($inputFiles, $expectedFile)
    {
        $layout = $this->_utility->getLayoutFromFixture($inputFiles, $this->_utility->getLayoutDependencies());
        $this->assertInstanceOf('Magento\Framework\View\LayoutInterface', $layout);
        $this->_assertLayoutUpdate($expectedFile, $layout->getUpdate());
    }

    public function getLayoutFromFixtureDataProvider()
    {
        return [
            'single fixture file' => [
                __DIR__ . '/_files/layout/handle_two.xml',
                __DIR__ . '/_files/layout_merged/single_handle.xml',
            ],
            'multiple fixture files' => [
                glob(__DIR__ . '/_files/layout/*.xml'),
                __DIR__ . '/_files/layout_merged/multiple_handles.xml',
            ]
        ];
    }
}
