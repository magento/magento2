<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Model\File\Storage;

use Magento\Framework\App\Request\Http;
use Magento\MediaStorage\Model\File\Storage\Request;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /**
     * @var Request
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var string
     */
    protected $_pathInfo = 'PathInfo';

    protected function setUp(): void
    {
        $path = '..PathInfo';
        $this->_requestMock = $this->createMock(Http::class);
        $this->_requestMock->expects($this->once())->method('getPathInfo')->willReturn($path);
        $this->_model = new Request($this->_requestMock);
    }

    protected function tearDown(): void
    {
        unset($this->_model);
        unset($this->_requestMock);
    }

    public function testGetPathInfo()
    {
        $this->assertEquals($this->_pathInfo, $this->_model->getPathInfo());
    }
}
