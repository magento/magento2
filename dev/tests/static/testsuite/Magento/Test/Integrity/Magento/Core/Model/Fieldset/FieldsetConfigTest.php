<?php
/**
 * Find "fieldset.xml" files and validate them
 *
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
namespace Magento\Test\Integrity\Magento\Core\Model\Fieldset;

class FieldsetConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testXmlFiles()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $configFile
             */
            function ($configFile) {
                $dom = new \DOMDocument();
                $dom->loadXML(file_get_contents($configFile));
                $schema = \Magento\TestFramework\Utility\Files::init()->getPathToSource() .
                    '/lib/internal/Magento/Framework/Object/etc/fieldset_file.xsd';
                $errors = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schema);
                if ($errors) {
                    $this->fail(
                        'XML-file ' . $configFile . ' has validation errors:' . PHP_EOL . implode(
                            PHP_EOL . PHP_EOL,
                            $errors
                        )
                    );
                }
            },
            \Magento\TestFramework\Utility\Files::init()->getConfigFiles('fieldset.xml', array(), true)
        );
    }

    public function testSchemaUsingValidXml()
    {
        $xmlFile = __DIR__ . '/_files/fieldset.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $schema = \Magento\TestFramework\Utility\Files::init()->getPathToSource() .
            '/lib/internal/Magento/Framework/Object/etc/fieldset.xsd';
        $errors = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schema);
        if ($errors) {
            $this->fail(
                'There is a problem with the schema.  A known good XML file failed validation: ' . PHP_EOL . implode(
                    PHP_EOL . PHP_EOL,
                    $errors
                )
            );
        }
    }

    public function testSchemaUsingInvalidXml()
    {
        $xmlFile = __DIR__ . '/_files/invalid_fieldset.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $schema = \Magento\TestFramework\Utility\Files::init()->getPathToSource() .
            '/lib/internal/Magento/Framework/Object/etc/fieldset.xsd';
        $errors = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schema);
        if (!$errors) {
            $this->fail('There is a problem with the schema.  A known bad XML file passed validation');
        }
    }

    public function testFileSchemaUsingValidXml()
    {
        $xmlFile = __DIR__ . '/_files/fieldset_file.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $schema = \Magento\TestFramework\Utility\Files::init()->getPathToSource() .
            '/lib/internal/Magento/Framework/Object/etc/fieldset_file.xsd';
        $errors = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schema);
        if ($errors) {
            $this->fail(
                'There is a problem with the schema.  A known good XML file failed validation: ' . PHP_EOL . implode(
                    PHP_EOL . PHP_EOL,
                    $errors
                )
            );
        }
    }

    public function testFileSchemaUsingInvalidXml()
    {
        $xmlFile = __DIR__ . '/_files/invalid_fieldset.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $schema = \Magento\TestFramework\Utility\Files::init()->getPathToSource() .
            '/lib/internal/Magento/Framework/Object/etc/fieldset_file.xsd';
        $errors = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schema);
        if (!$errors) {
            $this->fail('There is a problem with the schema.  A known bad XML file passed validation');
        }
    }
}
