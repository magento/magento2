<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

class CrontabConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * attributes represent merging rules
     * copied from original class \Magento\Framework\App\Route\Config\Reader
     *
     * @var array
     */
    protected $_idAttributes = ['/config/group' => 'id', '/config/group/job' => 'name'];

    /**
     * Path to tough XSD for merged file validation
     *
     * @var string
     */
    protected $_mergedSchemaFile;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_mergedSchemaFile = $objectManager->get('Magento\Cron\Model\Config\SchemaLocator')->getSchema();
    }

    public function testCrontabConfigFiles()
    {
        $invalidFiles = [];

        $files = \Magento\Framework\Test\Utility\Files::init()->getConfigFiles('crontab.xml');
        $mergedConfig = new \Magento\Framework\Config\Dom(
            '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></config>',
            $this->_idAttributes
        );

        foreach ($files as $file) {
            $content = file_get_contents($file[0]);
            try {
                new \Magento\Framework\Config\Dom($content, $this->_idAttributes);
                //merge won't be performed if file is invalid because of exception thrown
                $mergedConfig->merge($content);
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                $invalidFiles[] = $file[0];
            }
        }

        if (!empty($invalidFiles)) {
            $this->fail('Found broken files: ' . implode("\n", $invalidFiles));
        }

        $errors = [];
        $mergedConfig->validate($this->_mergedSchemaFile, $errors);
        if ($errors) {
            $this->fail('Merged routes config is invalid: ' . "\n" . implode("\n", $errors));
        }
    }
}
