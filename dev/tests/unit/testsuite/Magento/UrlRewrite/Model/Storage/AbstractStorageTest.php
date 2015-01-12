<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\Storage;

class AbstractStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlRewriteBuilder;

    /**
     * @var \Magento\UrlRewrite\Model\Storage\AbstractStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storage;

    protected function setUp()
    {
        $this->urlRewriteBuilder = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder')
            ->disableOriginalConstructor()->getMock();

        $this->storage = $this->getMockForAbstractClass(
            'Magento\UrlRewrite\Model\Storage\AbstractStorage',
            [$this->urlRewriteBuilder],
            '',
            true,
            true,
            true
        );
    }

    public function testFindAllByData()
    {
        $data = [['field1' => 'value1']];
        $rows = [['row1'], ['row2']];
        $urlRewrites = [['urlRewrite1'], ['urlRewrite2']];

        $this->storage->expects($this->once())
            ->method('doFindAllByData')
            ->with($data)
            ->will($this->returnValue($rows));

        $this->urlRewriteBuilder->expects($this->at(0))
            ->method('populateWithArray')
            ->with($rows[0])
            ->will($this->returnSelf());

        $this->urlRewriteBuilder->expects($this->at(1))
            ->method('create')
            ->will($this->returnValue($urlRewrites[0]));

        $this->urlRewriteBuilder->expects($this->at(2))
            ->method('populateWithArray')
            ->with($rows[1])
            ->will($this->returnSelf());

        $this->urlRewriteBuilder->expects($this->at(3))
            ->method('create')
            ->will($this->returnValue($urlRewrites[1]));

        $this->assertEquals($urlRewrites, $this->storage->findAllByData($data));
    }

    public function testFindOneByDataIfNotFound()
    {
        $data = [['field1' => 'value1']];

        $this->storage->expects($this->once())
            ->method('doFindOneByData')
            ->with($data)
            ->will($this->returnValue(null));

        $this->assertNull($this->storage->findOneByData($data));
    }

    public function testFindOneByDataIfFound()
    {
        $data = [['field1' => 'value1']];
        $row = ['row1'];
        $urlRewrite = ['urlRewrite1'];

        $this->storage->expects($this->once())
            ->method('doFindOneByData')
            ->with($data)
            ->will($this->returnValue($row));

        $this->urlRewriteBuilder->expects($this->once())
            ->method('populateWithArray')
            ->with($row)
            ->will($this->returnSelf());

        $this->urlRewriteBuilder->expects($this->any())
            ->method('create')
            ->will($this->returnValue($urlRewrite));

        $this->assertEquals($urlRewrite, $this->storage->findOneByData($data));
    }

    public function testReplaceIfUrlsAreEmpty()
    {
        $this->storage->expects($this->never())->method('doReplace');

        $this->storage->replace([]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage URL key for specified store already exists.
     */
    public function testReplaceIfThrewDuplicateEntryExceptionWithCustomMessage()
    {
        $this->storage
            ->expects($this->once())
            ->method('doReplace')
            ->will($this->throwException(new DuplicateEntryException('Custom storage message')));

        $this->storage->replace([['UrlRewrite1']]);
    }

    public function testReplace()
    {
        $urls = [['UrlRewrite1'], ['UrlRewrite2']];

        $this->storage
            ->expects($this->once())
            ->method('doReplace')
            ->with($urls);

        $this->storage->replace($urls);
    }
}
