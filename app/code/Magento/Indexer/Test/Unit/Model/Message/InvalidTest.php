<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\Message;

class InvalidTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\Indexer
     */
    private $indexerMock = null;

    /**
     * @var \Magento\Indexer\Model\Message\Invalid
     */
    protected $model;

    /**
     * Set up test
     */
    protected function setUp()
    {
        $collectionMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\Collection::class,
            ['getItems'],
            [],
            '',
            false
        );

        $this->indexerMock = $this->getMock(
            \Magento\Indexer\Model\Indexer::class,
            ['getStatus'],
            [],
            '',
            false
        );

        $urlBuilder = $this->getMock(
            \Magento\Framework\UrlInterface::class,
            [],
            [],
            '',
            false
        );

        $collectionMock->expects($this->any())->method('getItems')->with()->willReturn([$this->indexerMock]);

        $this->model = new \Magento\Indexer\Model\Message\Invalid(
            $collectionMock,
            $urlBuilder
        );
    }

    public function testDisplayMessage()
    {
        $this->indexerMock->expects($this->any())->method('getStatus')->with()
            ->willReturn(\Magento\Framework\Indexer\StateInterface::STATUS_INVALID);

        $this->assertTrue($this->model->isDisplayed());
    }

    public function testHideMessage()
    {
        $this->indexerMock->expects($this->any())->method('getStatus')->with()
            ->willReturn('Status other than "invalid"');

        $this->assertFalse($this->model->isDisplayed());
    }
}
