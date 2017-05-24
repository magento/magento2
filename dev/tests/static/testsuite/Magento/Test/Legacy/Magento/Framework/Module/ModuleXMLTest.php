<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Legacy\Magento\Framework\Module;

/**
 * Test for obsolete nodes/attributes in the module.xml
 */
class ModuleXMLTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @dataProvider moduleXmlDataProvider
     */
    public function testModuleXml($file)
    {
        $xml = simplexml_load_file($file);
        $this->assertEmpty(
            $xml->xpath('/config/module/@version'),
            'The "version" attribute is obsolete. Use "setup_version" instead.'
        );
        $this->assertEmpty(
            $xml->xpath('/config/module/@active'),
            'The "active" attribute is obsolete. The list of active modules is defined in deployment configuration.'
        );
    }

    /**
     * @return array
     */
    public function moduleXmlDataProvider()
    {
        return \Magento\Framework\App\Utility\Files::init()->getConfigFiles('module.xml');
    }
}
