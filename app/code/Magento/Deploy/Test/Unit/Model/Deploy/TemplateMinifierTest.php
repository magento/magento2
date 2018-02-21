<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\Deploy;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TemplateMinifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Deploy\Model\Deploy\TemplateMinifier
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Utility\Files
     */
    private $filesUtilsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Template\Html\MinifierInterface
     */
    private $minifierMock;

    protected function setUp()
    {
        $this->minifierMock = $this->getMock(
            \Magento\Framework\View\Template\Html\MinifierInterface::class,
            [],
            [],
            '',
            false
        );
        $this->filesUtilsMock = $this->getMock(\Magento\Framework\App\Utility\Files::class, [], [], '', false);

        $this->model = new \Magento\Deploy\Model\Deploy\TemplateMinifier(
            $this->filesUtilsMock,
            $this->minifierMock
        );
    }

    public function testMinifyTemplates()
    {
        $templateMock = "template.phtml";
        $templatesMock = [$templateMock];

        $this->filesUtilsMock->expects($this->once())->method('getPhtmlFiles')->with(false, false)
            ->willReturn($templatesMock);
        $this->minifierMock->expects($this->once())->method('minify')->with($templateMock);

        self::assertEquals(1, $this->model->minifyTemplates());
    }
}
