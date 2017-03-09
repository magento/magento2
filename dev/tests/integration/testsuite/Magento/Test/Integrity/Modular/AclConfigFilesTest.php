<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;


class AclConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Configuration acl file list
     *
     * @var array
     */
    protected $_fileList = [];

    /**
     * Path to scheme file
     *
     * @var string
     */
    protected $_schemeFile;

    protected function setUp()
    {
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->_schemeFile = $urnResolver->getRealPath('urn:magento:framework:Acl/etc/acl.xsd');
    }

    /**
     * Test each acl configuration file
     * @param string $file
     * @dataProvider aclConfigFileDataProvider
     */
    public function testAclConfigFile($file)
    {
        $validationStateMock = $this->getMock(
            \Magento\Framework\Config\ValidationStateInterface::class,
            [],
            [],
            '',
            false
        );
        $validationStateMock->method('isValidationRequired')
            ->willReturn(true);
        $domConfig = new \Magento\Framework\Config\Dom(file_get_contents($file), $validationStateMock);
        $result = $domConfig->validate($this->_schemeFile, $errors);
        $message = "Invalid XML-file: {$file}\n";
        foreach ($errors as $error) {
            $message .= "{$error}\n";
        }
        $this->assertTrue($result, $message);
    }

    /**
     * @return array
     */
    public function aclConfigFileDataProvider()
    {
        return \Magento\Framework\App\Utility\Files::init()->getConfigFiles('acl.xml');
    }
}
