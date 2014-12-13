<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Block\Adminhtml;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\DesignEditor\Block\Adminhtml\Theme::addButton
     * @covers \Magento\DesignEditor\Block\Adminhtml\Theme::clearButtons
     * @covers \Magento\DesignEditor\Block\Adminhtml\Theme::getButtonsHtml
     */
    public function testButtons()
    {
        $themeMock = $this->getMock('Magento\DesignEditor\Block\Adminhtml\Theme', null, [], '', false);
        $buttonMock = $this->getMock('StdClass', ['toHtml']);

        $buttonMock->expects($this->once())->method('toHtml')->will($this->returnValue('Block html data'));

        $themeMock->addButton($buttonMock);
        $this->assertEquals('Block html data', $themeMock->getButtonsHtml());

        $themeMock->clearButtons();
        $this->assertEquals('', $themeMock->getButtonsHtml());
    }
}
