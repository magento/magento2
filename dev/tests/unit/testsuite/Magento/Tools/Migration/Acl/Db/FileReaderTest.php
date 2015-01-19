<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl\Db;

require_once realpath(
    __DIR__ . '/../../../../../../../../'
) . '/tools/Magento/Tools/Migration/Acl/Db/FileReader.php';
class FileReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\Acl\Db\FileReader
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Tools\Migration\Acl\Db\FileReader();
    }

    public function testExtractData()
    {
        $filePath = __DIR__ . '/../_files/log/AclXPathToAclId.log';
        $expectedMap = [
            "admin/test1/test2" => "Test1_Test2::all",
            "admin/test1/test2/test3" => "Test1_Test2::test3",
            "admin/test1/test2/test4" => "Test1_Test2::test4",
            "admin/test1/test2/test5" => "Test1_Test2::test5",
            "admin/test6" => "Test6_Test6::all",
        ];
        $this->assertEquals($expectedMap, $this->_model->extractData($filePath));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExtractDataThrowsExceptionIfInvalidFileProvided()
    {
        $this->_model->extractData('invalidFile.log');
    }
}
