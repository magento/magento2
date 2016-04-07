<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Legacy tests to find obsolete system configuration declaration
 */
namespace Magento\Test\Legacy;

class ObsoleteSystemConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testSystemConfigurationDeclaration()
    {
        $fileList = \Magento\Framework\App\Utility\Files::init()->getConfigFiles(
            'system.xml',
            ['wsdl.xml', 'wsdl2.xml', 'wsi.xml'],
            false
        );
        foreach ($fileList as $configFile) {
            $configXml = simplexml_load_file($configFile);
            $xpath = '/config/tabs|/config/sections';
            $this->assertEmpty(
                $configXml->xpath($xpath),
                'Obsolete system configuration structure detected in file ' . $configFile . '.'
            );
        }
    }
}
