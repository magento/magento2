<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Reader;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Config\FileIterator;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\ReadFactory;

class DomTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Dom
     */
    private $dom;

    /**
     * @return void
     */
    public function testConfigurationDom()
    {
        $filename = 'test_component.xml';
        foreach ($this->getComponentFiles($filename) as $content) {
            if (!$this->dom) {
                $objectManager = Bootstrap::getObjectManager();

                $this->dom = $objectManager->create(Dom::class, ['xml' => $content]);
            } else {
                $this->dom->merge($content);
            }
        }
        $this->assertXmlStringEqualsXmlFile(
            $this->getMergedFilePath('test_component_merged.xml'),
            $this->dom->getDom()->saveXML()
        );
    }

    /**
     * @return void
     */
    public function testDefinitionDom()
    {
        $filename = 'etc/test_definition.xml';
        foreach ($this->getComponentFiles($filename) as $content) {
            if (!$this->dom) {
                $objectManager = Bootstrap::getObjectManager();

                $this->dom = $objectManager->create(
                    Dom::class,
                    [
                        'xml' => $content,
                        'idAttributes' => ['/' => 'name'],
                        'schemaLocator' => $objectManager->create(Definition\SchemaLocator::class)
                    ]
                );
            } else {
                $this->dom->merge($content);
            }
        }
        $this->assertXmlStringEqualsXmlFile(
            $this->getMergedFilePath('etc/test_definition_merged.xml'),
            $this->dom->getDom()->saveXML()
        );
    }

    /**
     * @param string $filename
     * @return \Magento\Framework\Config\FileIterator
     */
    private function getComponentFiles($filename)
    {
        $path = realpath(__DIR__ . '/../../_files/view');
        $paths = [
            $path . '/module_one/ui_component/' . $filename,
            $path . '/module_two/ui_component/' . $filename
        ];
        return new FileIterator(new ReadFactory(new DriverPool), $paths);
    }

    /**
     * @param $filename
     * @return string
     */
    private function getMergedFilePath($filename)
    {
        return realpath(__DIR__ . '/../../_files/view/ui_component') . DIRECTORY_SEPARATOR. $filename;
    }
}
