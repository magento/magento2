<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

class EventConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_schemaFile = $objectManager->get('Magento\Framework\Event\Config\SchemaLocator')->getSchema();
    }

    /**
     * @param string $file
     * @dataProvider eventConfigFilesDataProvider
     */
    public function testEventConfigFiles($file)
    {
        $errors = [];
        $dom = new \Magento\Framework\Config\Dom(file_get_contents($file));
        $result = $dom->validate($this->_schemaFile, $errors);
        $message = "Invalid XML-file: {$file}\n";
        foreach ($errors as $error) {
            $message .= "{$error->message} Line: {$error->line}\n";
        }
        $this->assertTrue($result, $message);
    }

    /**
     * @return array
     */
    public function eventConfigFilesDataProvider()
    {
        return \Magento\Framework\Test\Utility\Files::init()->getConfigFiles('{*/events.xml,events.xml}');
    }
}
