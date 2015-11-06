<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

use Magento\Framework\Component\ComponentRegistrar;

class RouteConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Config\ValidationStateInterface
     */

    protected $validationStateMock;
    /**
     * attributes represent merging rules
     * copied from original class \Magento\Framework\App\Route\Config\Reader
     * @var array
     */
    protected $_idAttributes = [
        '/config/routers' => 'id',
        '/config/routers/route' => 'id',
        '/config/routers/route/module' => 'name',
    ];

    /**
     * Path to loose XSD for per file validation
     *
     * @var string
     */
    protected $schemaFile;

    /**
     * Path to tough XSD for merged file validation
     *
     * @var string
     */
    protected $mergedSchemaFile;

    protected function setUp()
    {
        $this->validationStateMock = $this->getMock(
            '\Magento\Framework\Config\ValidationStateInterface',
            [],
            [],
            '',
            false
        );
        $this->validationStateMock->method('isValidationRequired')
            ->willReturn(true);
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->schemaFile = $urnResolver->getRealPath('urn:magento:framework:App/etc/routes.xsd');
        $this->mergedSchemaFile = $urnResolver->getRealPath('urn:magento:framework:App/etc/routes_merged.xsd');
    }

    public function testRouteConfigsValidation()
    {
        $invalidFiles = [];

        $componentRegistrar = new ComponentRegistrar();
        $files = [];
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            $mask = $moduleDir . '/etc/*/routes.xml';
            $files = array_merge($files, glob($mask));
        }
        $mergedConfig = new \Magento\Framework\Config\Dom(
            '<config></config>',
            $this->validationStateMock,
            $this->_idAttributes
        );

        foreach ($files as $file) {
            $content = file_get_contents($file);
            try {
                new \Magento\Framework\Config\Dom(
                    $content,
                    $this->validationStateMock,
                    $this->_idAttributes,
                    null,
                    $this->schemaFile
                );

                //merge won't be performed if file is invalid because of exception thrown
                $mergedConfig->merge($content);
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                $invalidFiles[] = $file;
            }
        }

        if (!empty($invalidFiles)) {
            $this->fail('Found broken files: ' . implode("\n", $invalidFiles));
        }

        try {
            $errors = [];
            $mergedConfig->validate($this->mergedSchemaFile, $errors);
        } catch (\Exception $e) {
            $this->fail('Merged routes config is invalid: ' . "\n" . implode("\n", $errors));
        }
    }
}
