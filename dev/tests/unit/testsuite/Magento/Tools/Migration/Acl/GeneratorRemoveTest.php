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
 * Tools_Migration_Acl_Generator remove test case
 */
class GeneratorRemoveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $model \Magento\Tools\Migration\Acl\Generator
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_emptyFile;

    /**
     * @var string
     */
    protected $_notEmptyFile;

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
        $fixturePath = realpath(__DIR__) . '/_files';
        $path = $fixturePath . '/remove/';

        $this->_emptyFile = $path . 'empty.xml';
        $this->_notEmptyFile = $path . 'not_empty.xml';

        $this->_xmlFormatterMock = $this->getMock('Magento\Tools\Migration\Acl\Formatter');
        $this->_fileManagerMock = $this->getMock('Magento\Tools\Migration\Acl\FileManager');
        $this->_fileManagerMock->expects($this->once())->method('remove')->with($this->equalTo($this->_emptyFile));
        $this->_model = new \Magento\Tools\Migration\Acl\Generator($this->_xmlFormatterMock, $this->_fileManagerMock);
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testRemoveAdminhtmlFiles()
    {
        $domEmpty = new \DOMDocument();
        $domEmpty->load($this->_emptyFile);

        $domNotEmpty = new \DOMDocument();
        $domNotEmpty->load($this->_notEmptyFile);

        $adminhtmlDomList = [$this->_emptyFile => $domEmpty, $this->_notEmptyFile => $domNotEmpty];

        $this->_model->setAdminhtmlDomList($adminhtmlDomList);
        $expected = [
            'removed' => [$this->_emptyFile],
            'not_removed' => [$this->_notEmptyFile],
            'artifacts' => ['AclXPathToAclId.log' => json_encode([])],
        ];

        $result = $this->_model->removeAdminhtmlFiles();
        $this->assertEquals($expected, $result);
    }
}
