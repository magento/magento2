<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

class ViewConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @dataProvider viewConfigFileDataProvider
     */
    public function testViewConfigFile($file)
    {
        /** @var \Magento\Framework\View\Xsd\Reader $reader */
        $reader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\Xsd\Reader'
        );
        $mergeXsd = $reader->read();
        $domConfig = new \Magento\Framework\Config\Dom($file);
        $result = $domConfig->validate(
            $mergeXsd,
            $errors
        );
        $this->assertTrue($result, "Invalid XML-file: {$file}\n" . join("\n", $errors));
    }

    /**
     * @return array
     */
    public function viewConfigFileDataProvider()
    {
        $result = [];
        $files = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Module\Dir\Reader'
        )->getConfigurationFiles(
            'view.xml'
        );
        foreach ($files as $file) {
            $result[] = [$file];
        }
        return $result;
    }
}
