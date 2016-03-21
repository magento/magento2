<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Block\Adminhtml\Promo\Quote\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\RegistryConstants;

class GenericButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\GenericButton
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    protected function setUp()
    {
        $this->urlBuilderMock = $this->getMock('\Magento\Framework\UrlInterface', [], [], '', false);
        $this->registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $contextMock = $this->getMock('\Magento\Backend\Block\Widget\Context', [], [], '', false);

        $contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = (new ObjectManager($this))->getObject(
            'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\GenericButton',
            [
                'context' => $contextMock,
                'registry' => $this->registryMock
            ]
        );
    }

    public function testCanRender()
    {
        $name = "Catalog Rule";
        $this->assertEquals($name, $this->model->canRender($name));
    }

    public function testGetUrl()
    {
        $url = "http://magento.com/salesRule/";
        $route = 'button';
        $params = ['unit' => 'test'];

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with($route, $params)
            ->willReturn($url);

        $this->assertEquals($url, $this->model->getUrl($route, $params));
    }

    public function testGetRuleId()
    {
        $ruleId = 42;
        $ruleMock = new \Magento\Framework\DataObject(['id' => $ruleId]);
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(RegistryConstants::CURRENT_SALES_RULE)
            ->willReturn($ruleMock);

        $this->assertEquals($ruleId, $this->model->getRuleId());
    }

    public function testGetRuleIdWithoutRule()
    {
        $this->assertNull($this->model->getRuleId());
    }
}
