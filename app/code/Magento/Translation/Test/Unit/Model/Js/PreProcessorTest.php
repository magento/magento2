<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Model\Js;

use Magento\Translation\Model\Js\PreProcessor;
use Magento\Translation\Model\Js\Config;

class PreProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PreProcessor
     */
    protected $model;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    protected function setUp()
    {
        $this->configMock = $this->getMock('Magento\Translation\Model\Js\Config', [], [], '', false);
        $this->model = new PreProcessor($this->configMock);
    }

    public function testGetData()
    {
        $chain = $this->getMock('Magento\Framework\View\Asset\PreProcessor\Chain', [], [], '', false);
        $originalContent = 'content$.mage.__("hello1")content';
        $translatedContent = 'content"hello1"content';
        $patterns = ['~\$\.mage\.__\([\'|\"](.+?)[\'|\"]\)~'];

        $this->configMock->expects($this->once())
            ->method('isEmbeddedStrategy')
            ->willReturn(true);
        $chain->expects($this->once())
            ->method('getContent')
            ->willReturn($originalContent);
        $this->configMock->expects($this->once())
            ->method('getPatterns')
            ->willReturn($patterns);

        $chain->expects($this->once())
            ->method('setContent')
            ->with($translatedContent);

        $this->model->process($chain);
    }
}
