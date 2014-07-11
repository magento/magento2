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

namespace Magento\Framework\App\Helper;

/**
 * Class AbstractHelperTest
 */
class AbstractHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\Helper\AbstractHelper */
    protected $helper;

    /** @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject */
    protected $moduleManagerMock;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilderMock;

    protected function setUp()
    {
        $this->urlBuilderMock = $this->getMock('Magento\Framework\UrlInterface', [], [], '', false);
        $this->moduleManagerMock = $this->getMock('Magento\Framework\Module\Manager', [], [], '', false);
        $this->contextMock = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->contextMock->expects($this->once())
            ->method('getModuleManager')
            ->will($this->returnValue($this->moduleManagerMock));
        $this->contextMock->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($this->urlBuilderMock));

        $this->helper = $this->getMockForAbstractClass(
            'Magento\Framework\App\Helper\AbstractHelper',
            ['context' => $this->contextMock]
        );
    }

    /**
     * @covers \Magento\Framework\App\Helper\AbstractHelper::isModuleEnabled
     * @covers \Magento\Framework\App\Helper\AbstractHelper::isModuleOutputEnabled
     * @param string|null $moduleName
     * @param string $requestedName
     * @param bool $result
     * @dataProvider isModuleEnabledDataProvider
     */
    public function testIsModuleEnabled($moduleName, $requestedName, $result)
    {
        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo($requestedName))
            ->will($this->returnValue($result));

        $this->moduleManagerMock->expects($this->once())
            ->method('isOutputEnabled')
            ->with($this->equalTo($requestedName))
            ->will($this->returnValue($result));
        $this->assertSame($result, $this->helper->isModuleEnabled($moduleName));
        $this->assertSame($result, $this->helper->isModuleOutputEnabled($moduleName));
    }

    /**
     * @return array
     */
    public function isModuleEnabledDataProvider()
    {
        return [
            [null, '', true],
            [null, '', false],
            ['Module_Name', 'Module_Name', false],
            ['Module\\Name', 'Module\\Name', true],
        ];
    }

    /**
     * @covers \Magento\Framework\App\Helper\AbstractHelper::urlEncode
     * @covers \Magento\Framework\App\Helper\AbstractHelper::urlDecode
     */
    public function testUrlDecode()
    {
        $data = uniqid();
        $result = $this->helper->urlEncode($data);
        $this->urlBuilderMock->expects($this->once())
            ->method('sessionUrlVar')
            ->with($this->equalTo($data))
            ->will($this->returnValue($result));
        $this->assertNotContains('&', $result);
        $this->assertNotContains('%', $result);
        $this->assertNotContains('+', $result);
        $this->assertNotContains('=', $result);
        $this->assertEquals($result, $this->helper->urlDecode($result));
    }
}
