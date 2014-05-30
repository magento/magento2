<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity\Modular;

class RouteConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * attributes represent merging rules
     * copied from original class \Magento\Framework\App\Route\Config\Reader
     * @var array
     */
    protected $_idAttributes = array(
        '/config/routers' => 'id',
        '/config/routers/route' => 'id',
        '/config/routers/route/module' => 'name'
    );

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
        global $magentoBaseDir;

        $this->_schemaFile = $magentoBaseDir . '/lib/internal/Magento/Framework/App/etc/routes.xsd';
        $this->_mergedSchemaFile = $magentoBaseDir . '/lib/internal/Magento/Framework/App/etc/routes_merged.xsd';
    }

    public function testRouteConfigsValidation()
    {
        global $magentoBaseDir;
        $invalidFiles = array();

        $mask = $magentoBaseDir . '/app/code/*/*/etc/*/routes.xml';
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
            $errors = array();
            $mergedConfig->validate($this->_mergedSchemaFile, $errors);
        } catch (\Exception $e) {
            $this->fail('Merged routes config is invalid: ' . "\n" . implode("\n", $errors));
        }
    }
}
