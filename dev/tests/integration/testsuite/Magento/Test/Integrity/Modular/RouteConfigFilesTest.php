<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

class RouteConfigFilesTest extends \PHPUnit_Framework_TestCase
{
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
    protected $_schemaFile;

    /**
     * Path to tough XSD for merged file validation
     *
     * @var string
     */
    protected $_mergedSchemaFile;

    protected function setUp()
    {
        $this->_schemaFile = BP . '/lib/internal/Magento/Framework/App/etc/routes.xsd';
        $this->_mergedSchemaFile = BP . '/lib/internal/Magento/Framework/App/etc/routes_merged.xsd';
    }

    public function testRouteConfigsValidation()
    {
        $invalidFiles = [];

        $mask = BP . '/app/code/*/*/etc/*/routes.xml';
        $files = glob($mask);
        $mergedConfig = new \Magento\Framework\Config\Dom('<config></config>', $this->_idAttributes);

        foreach ($files as $file) {
            $content = file_get_contents($file);
            try {
                new \Magento\Framework\Config\Dom($content, $this->_idAttributes, null, $this->_schemaFile);

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
            $mergedConfig->validate($this->_mergedSchemaFile, $errors);
        } catch (\Exception $e) {
            $this->fail('Merged routes config is invalid: ' . "\n" . implode("\n", $errors));
        }
    }
}
