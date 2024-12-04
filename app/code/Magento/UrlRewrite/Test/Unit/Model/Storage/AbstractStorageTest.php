<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Model\Storage;

use Magento\Framework\Api\DataObjectHelper;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\Storage\AbstractStorage;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractStorageTest extends TestCase
{
    /**
     * @var UrlRewriteFactory|MockObject
     */
    protected $urlRewriteFactory;

    /**
     * @var DataObjectHelper|MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var AbstractStorage|MockObject
     */
    protected $storage;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->urlRewriteFactory = $this->getMockBuilder(UrlRewriteFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storage = $this->getMockForAbstractClass(
            AbstractStorage::class,
            [$this->urlRewriteFactory, $this->dataObjectHelper],
            '',
            true,
            true,
            true
        );
    }

    /**
     * @return void
     */
    public function testFindAllByData(): void
    {
        $data = [['field1' => 'value1']];
        $rows = [['row1'], ['row2']];
        $urlRewrites = [['urlRewrite1'], ['urlRewrite2']];

        $this->storage->expects($this->once())
            ->method('doFindAllByData')
            ->with($data)
            ->willReturn($rows);

            $this->dataObjectHelper
                ->method('populateWithArray')
                ->willReturnCallback(function ($arg1, $arg2, $arg3) use ($urlRewrites, $rows) {
                    if ($arg1 === $urlRewrites[0] && $arg2 === $rows[0] && $arg3 === UrlRewrite::class) {
                        return $this->dataObjectHelper;
                    } elseif ($arg1 === $urlRewrites[1] && $arg2 === $rows[1] && $arg3 === UrlRewrite::class) {
                        return $this->dataObjectHelper;
                    }
                });

        $this->urlRewriteFactory
            ->method('create')
            ->willReturnOnConsecutiveCalls($urlRewrites[0], $urlRewrites[1]);

        $this->assertEquals($urlRewrites, $this->storage->findAllByData($data));
    }

    /**
     * @return void
     */
    public function testFindOneByDataIfNotFound(): void
    {
        $data = [['field1' => 'value1']];

        $this->storage->expects($this->once())
            ->method('doFindOneByData')
            ->with($data)
            ->willReturn(null);

        $this->assertNull($this->storage->findOneByData($data));
    }

    /**
     * @return void
     */
    public function testFindOneByDataIfFound(): void
    {
        $data = [['field1' => 'value1']];
        $row = ['row1'];
        $urlRewrite = ['urlRewrite1'];

        $this->storage->expects($this->once())
            ->method('doFindOneByData')
            ->with($data)
            ->willReturn($row);

        $this->dataObjectHelper->expects($this->once())
            ->method('populateWithArray')
            ->with($urlRewrite, $row, UrlRewrite::class)->willReturnSelf();

        $this->urlRewriteFactory->expects($this->any())
            ->method('create')
            ->willReturn($urlRewrite);

        $this->assertEquals($urlRewrite, $this->storage->findOneByData($data));
    }

    /**
     * @return void
     */
    public function testReplaceIfUrlsAreEmpty(): void
    {
        $this->storage->expects($this->never())->method('doReplace');

        $this->storage->replace([]);
    }

    /**
     * @return void
     */
    public function testReplaceIfThrewDuplicateEntryExceptionWithCustomMessage(): void
    {
        $this->expectException('Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException');
        $this->expectExceptionMessage('Custom storage message');
        $this->storage
            ->expects($this->once())
            ->method('doReplace')
            ->willThrowException(new UrlAlreadyExistsException(__('Custom storage message')));

        $this->storage->replace([['UrlRewrite1']]);
    }

    /**
     * @return void
     */
    public function testReplaceIfThrewDuplicateEntryExceptionDefaultMessage(): void
    {
        $this->expectException('Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException');
        $this->expectExceptionMessage('URL key for specified store already exists');
        $this->storage
            ->expects($this->once())
            ->method('doReplace')
            ->willThrowException(new UrlAlreadyExistsException());

        $this->storage->replace([['UrlRewrite1']]);
    }

    /**
     * @return void
     */
    public function testReplace(): void
    {
        $urls = [['UrlRewrite1'], ['UrlRewrite2']];

        $this->storage
            ->expects($this->once())
            ->method('doReplace')
            ->with($urls);

        $this->storage->replace($urls);
    }
}
