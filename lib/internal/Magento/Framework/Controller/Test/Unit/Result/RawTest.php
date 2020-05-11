<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Controller\Test\Unit\Result;

use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RawTest extends TestCase
{
    /** @var Raw */
    protected $raw;

    /** @var HttpResponseInterface|MockObject*/
    protected $response;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->response = $this->getMockForAbstractClass(HttpResponseInterface::class);
        $this->raw = $this->objectManagerHelper->getObject(Raw::class);
    }

    public function testSetContents()
    {
        $content = '<content>test</content>';
        $this->assertInstanceOf(Raw::class, $this->raw->setContents($content));
    }

    public function testRender()
    {
        $content = '<content>test</content>';
        $this->raw->setContents($content);
        $this->response->expects($this->once())->method('setBody')->with($content);
        $this->assertSame($this->raw, $this->raw->renderResult($this->response));
    }
}
