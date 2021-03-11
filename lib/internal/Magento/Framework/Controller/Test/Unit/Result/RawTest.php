<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Test\Unit\Result;

use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RawTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Controller\Result\Raw */
    protected $raw;

    /** @var HttpResponseInterface|\PHPUnit\Framework\MockObject\MockObject*/
    protected $response;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->response = $this->getMockForAbstractClass(HttpResponseInterface::class);
        $this->raw = $this->objectManagerHelper->getObject(\Magento\Framework\Controller\Result\Raw::class);
    }

    public function testSetContents()
    {
        $content = '<content>test</content>';
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Raw::class, $this->raw->setContents($content));
    }

    public function testRender()
    {
        $content = '<content>test</content>';
        $this->raw->setContents($content);
        $this->response->expects($this->once())->method('setBody')->with($content);
        $this->assertSame($this->raw, $this->raw->renderResult($this->response));
    }
}
