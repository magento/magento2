<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor;

class LessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Less\FileGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileGenerator;

    /**
     * @var \Magento\Framework\Css\PreProcessor\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \Magento\Framework\View\Asset\PreProcessor\Chain
     */
    private $chain;

    /**
     * @var \Magento\Framework\Css\PreProcessor\Less
     */
    private $object;

    protected function setUp()
    {
        $this->fileGenerator = $this->getMock('\Magento\Framework\Less\FileGenerator', [], [], '', false);
        $this->adapter = $this->getMockForAbstractClass('\Magento\Framework\Css\PreProcessor\AdapterInterface');
        $asset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $asset->expects($this->once())->method('getContentType')->will($this->returnValue('origType'));
        $this->chain = new \Magento\Framework\View\Asset\PreProcessor\Chain($asset, 'original content', 'origType');
        $this->object = new \Magento\Framework\Css\PreProcessor\Less($this->fileGenerator, $this->adapter);
    }

    public function testProcess()
    {
        $expectedContent = 'updated content';
        $tmpFile = 'tmp/file.ext';
        $this->fileGenerator->expects($this->once())
            ->method('generateLessFileTree')
            ->with($this->chain)
            ->will($this->returnValue($tmpFile));
        $this->adapter->expects($this->once())
            ->method('process')
            ->with($tmpFile)
            ->will($this->returnValue($expectedContent));
        $this->object->process($this->chain);
        $this->assertEquals($expectedContent, $this->chain->getContent());
        $this->assertEquals('css', $this->chain->getContentType());
    }
}
