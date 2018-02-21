<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Test\Unit\Result;

/**
 * Class JsonTest
 *
 * @covers \Magento\Framework\Controller\Result\Json
 */
class JsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testRenderResult()
    {
        $json = '{"data":"data"}';
        $translatedJson = '{"data_translated":"data_translated"}';

        /** @var \Magento\Framework\Translate\InlineInterface|\PHPUnit_Framework_MockObject_MockObject
         * $translateInline
         */
        $translateInline = $this->getMock('Magento\Framework\Translate\InlineInterface', [], [], '', false);
        $translateInline->expects($this->any())->method('processResponseBody')->with($json, true)->will(
            $this->returnValue($translatedJson)
        );

        $response = $this->getMock('Magento\Framework\App\Response\Http', ['representJson'], [], '', false);
        $response->expects($this->atLeastOnce())->method('representJson')->with($json)->will($this->returnSelf());

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\Controller\Result\Json', ['translateInline' => $translateInline]);
        $resultJson->setJsonData($json);
        $this->assertSame($resultJson, $resultJson->renderResult($response));
    }
}
