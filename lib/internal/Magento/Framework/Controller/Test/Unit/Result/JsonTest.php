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
        $translateInline = $this->getMock(\Magento\Framework\Translate\InlineInterface::class);
        $translateInline->expects($this->any())->method('processResponseBody')->with($json, true)->will(
            $this->returnValue($translatedJson)
        );

        $response = $this->getMock(\Magento\Framework\App\Response\HttpInterface::class);
        $response->expects($this->atLeastOnce())->method('setHeader')->with('Content-Type', 'application/json', true);
        $response->expects($this->atLeastOnce())->method('setBody')->with($json);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(\Magento\Framework\Controller\Result\Json::class, ['translateInline' => $translateInline]);
        $resultJson->setJsonData($json);
        $this->assertSame($resultJson, $resultJson->renderResult($response));
    }
}
