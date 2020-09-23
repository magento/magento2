<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Controller\Test\Unit\Result;

use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\InlineInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * @covers \Magento\Framework\Controller\Result\Json
 */
class JsonTest extends TestCase
{
    /**
     * @return void
     */
    public function testRenderResult()
    {
        $json = '{"data":"data"}';
        $translatedJson = '{"data_translated":"data_translated"}';

        /** @var InlineInterface|MockObject
         * $translateInline
         */
        $translateInline = $this->getMockForAbstractClass(InlineInterface::class);
        $translateInline->expects($this->any())->method('processResponseBody')->with($json, true)->willReturn(
            $translatedJson
        );

        $response = $this->getMockForAbstractClass(HttpInterface::class);
        $response->expects($this->atLeastOnce())->method('setHeader')->with('Content-Type', 'application/json', true);
        $response->expects($this->atLeastOnce())->method('setBody')->with($json);

        /** @var Json $resultJson */
        $resultJson = (new ObjectManager($this))
            ->getObject(Json::class, ['translateInline' => $translateInline]);
        $resultJson->setJsonData($json);
        $this->assertSame($resultJson, $resultJson->renderResult($response));
    }
}
