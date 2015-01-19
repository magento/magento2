<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTypeDataProvider
     * @param string $templateType
     * @param int $expectedResult
     */
    public function testGetType($templateType, $expectedResult)
    {
        $emailConfig = $this->getMockBuilder(
            '\Magento\Email\Model\Template\Config'
        )->setMethods(
            ['getTemplateType']
        )->disableOriginalConstructor()->getMock();
        $emailConfig->expects($this->once())->method('getTemplateType')->will($this->returnValue($templateType));
        /** @var \Magento\Email\Model\Template $model */
        $model = $this->getMockBuilder(
            'Magento\Email\Model\Template'
        )->setMethods(
            ['_init']
        )->setConstructorArgs(
            [
                $this->getMock('Magento\Framework\Model\Context', [], [], '', false),
                $this->getMock('Magento\Core\Model\View\Design', [], [], '', false),
                $this->getMock('Magento\Framework\Registry', [], [], '', false),
                $this->getMock('Magento\Core\Model\App\Emulation', [], [], '', false),
                $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false),
                $this->getMock('Magento\Framework\Filesystem', [], [], '', false),
                $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false),
                $this->getMock('Magento\Framework\View\FileSystem', [], [], '', false),
                $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface'),
                $this->getMock('Magento\Email\Model\Template\FilterFactory', [], [], '', false),
                $emailConfig,
                ['template_id' => 10],
            ]
        )->getMock();
        $this->assertEquals($expectedResult, $model->getType());
    }

    public function getTypeDataProvider()
    {
        return [['text', 1], ['html', 2]];
    }
}
