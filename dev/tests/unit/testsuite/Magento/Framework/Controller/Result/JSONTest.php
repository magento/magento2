<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Controller\Result;

/**
 * Class JSONTest
 *
 * @covers Magento\Framework\Controller\Result\JSON
 */
class JSONTest extends \PHPUnit_Framework_TestCase
{
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

        /** @var \Magento\Framework\Controller\Result\JSON $resultJson */
        $resultJson = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\Controller\Result\JSON', ['translateInline' => $translateInline]);
        $resultJson->setJsonData($json);
        $this->assertSame($resultJson, $resultJson->renderResult($response));
    }
}
