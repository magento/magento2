<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl;

require_once realpath(__DIR__ . '/../../../../../../../') . '/tools/Magento/Tools/Migration/Acl/Generator.php';
require_once realpath(__DIR__ . '/../../../../../../../') . '/tools/Magento/Tools/Migration/Acl/FileManager.php';
require_once realpath(__DIR__ . '/../../../../../../../') . '/tools/Magento/Tools/Migration/Acl/Formatter.php';
/**
 * Tools_Migration_Acl test case
 */
class GeneratorSaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $model \Magento\Tools\Migration\Acl\Generator
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_fixturePath;

    /**
     * @var string
     */
    protected $_originFile;

    /**
     * @var string
     */
    protected $_aclFile;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_xmlFormatterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileManagerMock;

    protected function setUp()
    {
        $this->_xmlFormatterMock = $this->getMock('Magento\Tools\Migration\Acl\Formatter');
        $this->_fileManagerMock = $this->getMock('Magento\Tools\Migration\Acl\FileManager');
        $this->_model = new \Magento\Tools\Migration\Acl\Generator($this->_xmlFormatterMock, $this->_fileManagerMock);

        $this->_fixturePath = realpath(__DIR__) . '/_files';
        $path = $this->_fixturePath . '/save/';

        $this->_originFile = $path . 'adminhtml.xml';
        $this->_aclFile = $path . 'adminhtml/acl.xml';

        $dom = new \DOMDocument();
        $config = $dom->createElement('config');
        $dom->appendChild($config);
        $acl = $dom->createElement('acl');
        $config->appendChild($acl);
        $resources = $dom->createElement('resources');
        $acl->appendChild($resources);

        $resource1 = $dom->createElement('resource');
        $resource1->setAttribute('id', 'Map_Module::admin');
        $resources->appendChild($resource1);

        $resource2 = $dom->createElement('resource');
        $resource2->setAttribute('id', 'Module_One::customer');
        $resource2->setAttribute('title', 'Customers');
        $resource2->setAttribute('sortOrder', '40');
        $resource1->appendChild($resource2);

        $resource3 = $dom->createElement('resource');
        $resource3->setAttribute('id', 'Module_Two::group');
        $resource3->setAttribute('title', 'Customer Groups');
        $resource3->setAttribute('sortOrder', '10');
        $resource2->appendChild($resource3);

        $this->_model->setParsedDomList([$this->_originFile => $dom]);
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_xmlFormatterMock);
        unset($this->_fileManagerMock);
    }

    public function testSaveAclFiles()
    {
        $domList = $this->_model->getParsedDomList();
        $dom = clone $domList[$this->_originFile];
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $this->_xmlFormatterMock->expects(
            $this->once()
        )->method(
            'parseString'
        )->with(
            $dom->saveXml(),
            [
                'indent' => true,
                'input-xml' => true,
                'output-xml' => true,
                'add-xml-space' => false,
                'indent-spaces' => 4,
                'wrap' => 1000
            ]
        )->will(
            $this->returnCallback(
                function ($string) {
                    return 'formatted' . $string;
                }
            )
        );

        $this->_fileManagerMock->expects(
            $this->once()
        )->method(
            'write'
        )->with(
            $this->equalTo($this->_aclFile),
            $this->stringStartsWith('formatted')
        );

        $this->_model->saveAclFiles();
    }
}
