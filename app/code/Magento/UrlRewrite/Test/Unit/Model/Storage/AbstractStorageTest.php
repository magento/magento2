<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Test\Unit\Model\Storage;

class AbstractStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlRewriteFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\UrlRewrite\Model\Storage\AbstractStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storage;

    protected function setUp()
    {
        $this->urlRewriteFactory = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->dataObjectHelper = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()->getMock();

        $this->storage = $this->getMockForAbstractClass(
            \Magento\UrlRewrite\Model\Storage\AbstractStorage::class,
            [$this->urlRewriteFactory, $this->dataObjectHelper],
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

        $this->dataObjectHelper->expects($this->at(0))
            ->method('populateWithArray')
            ->with($urlRewrites[0], $rows[0], \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->will($this->returnSelf());

        $this->urlRewriteFactory->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($urlRewrites[0]));

        $this->dataObjectHelper->expects($this->at(1))
            ->method('populateWithArray')
            ->with($urlRewrites[1], $rows[1], \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->will($this->returnSelf());

        $this->urlRewriteFactory->expects($this->at(1))
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

        $this->dataObjectHelper->expects($this->once())
            ->method('populateWithArray')
            ->with($urlRewrite, $row, \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->will($this->returnSelf());

        $this->urlRewriteFactory->expects($this->any())
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
     * @expectedException \Magento\Framework\Exception\AlreadyExistsException
     * @expectedExceptionMessage URL key for specified store already exists.
     */
    public function testReplaceIfThrewDuplicateEntryExceptionWithCustomMessage()
    {
        $this->storage
            ->expects($this->once())
            ->method('doReplace')
            ->will($this->throwException(
                new \Magento\Framework\Exception\AlreadyExistsException(__('Custom storage message'))
            ));

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
