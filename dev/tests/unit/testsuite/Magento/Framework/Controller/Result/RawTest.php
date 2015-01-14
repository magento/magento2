<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Result;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class RawTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Controller\Result\Raw */
    protected $raw;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject*/
    protected $response;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->response = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            ['setBody', 'sendResponse'],
            [],
            '',
            false
        );
        $this->raw = $this->objectManagerHelper->getObject(
            'Magento\Framework\Controller\Result\Raw'
        );
    }

    public function testSetContents()
    {
        $content = '<content>test</content>';
        $this->assertInstanceOf('Magento\Framework\Controller\Result\Raw', $this->raw->setContents($content));
    }

    public function testRender()
    {
        $content = '<content>test</content>';
        $this->raw->setContents($content);
        $this->response->expects($this->once())->method('setBody')->with($content);
        $this->assertSame($this->raw, $this->raw->renderResult($this->response));
    }
}
