<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Message;

use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Indexer\Collection;
use Magento\Indexer\Model\Message\Invalid;
use PHPUnit\Framework\TestCase;

class InvalidTest extends TestCase
{
    /**
     * @var Indexer
     */
    private $indexerMock = null;

    /**
     * @var Invalid
     */
    protected $model;

    /**
     * Set up test
     */
    protected function setUp(): void
    {
        $collectionMock = $this->createPartialMock(Collection::class, ['getItems']);

        $this->indexerMock = $this->createPartialMock(Indexer::class, ['getStatus']);

        $urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);

        $collectionMock->expects($this->any())->method('getItems')->with()->willReturn([$this->indexerMock]);

        $this->model = new Invalid(
            $collectionMock,
            $urlBuilder
        );
    }

    public function testDisplayMessage()
    {
        $this->indexerMock->expects($this->any())->method('getStatus')->with()
            ->willReturn(StateInterface::STATUS_INVALID);

        $this->assertTrue($this->model->isDisplayed());
    }

    public function testHideMessage()
    {
        $this->indexerMock->expects($this->any())->method('getStatus')->with()
            ->willReturn('Status other than "invalid"');

        $this->assertFalse($this->model->isDisplayed());
    }
}
