<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Config;

class FileSystemReaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  FilesystemReader */
    protected $object;

    protected function setUp()
    {
        $fileResolver = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Search\Request\Config\FileResolverStub::class
        );
        $this->object = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Search\Request\Config\FilesystemReader::class,
            ['fileResolver' => $fileResolver]
        );
    }

    public function testRead()
    {
        $result = $this->object->read();
        // Filter values added by \Magento\CatalogSearch\Model\Search\ReaderPlugin
        $result = array_intersect_key($result, array_flip(['bool_query', 'filter_query', 'new_match_query']));
        $expected = include __DIR__ . '/../../_files/search_request_merged.php';
        $this->assertEquals($expected, $result);
    }
}
