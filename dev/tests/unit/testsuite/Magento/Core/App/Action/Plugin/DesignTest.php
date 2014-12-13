<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Core\App\Action\Plugin;

class DesignTest extends \PHPUnit_Framework_TestCase
{
    public function testAroundDispatch()
    {
        $subjectMock = $this->getMock('Magento\Framework\App\Action\Action', [], [], '', false);
        $designLoaderMock = $this->getMock('Magento\Framework\View\DesignLoader', [], [], '', false);
        $closureMock = function () {
            return 'Expected';
        };
        $requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $plugin = new \Magento\Core\App\Action\Plugin\Design($designLoaderMock);
        $designLoaderMock->expects($this->once())->method('load');
        $this->assertEquals('Expected', $plugin->aroundDispatch($subjectMock, $closureMock, $requestMock));
    }
}
