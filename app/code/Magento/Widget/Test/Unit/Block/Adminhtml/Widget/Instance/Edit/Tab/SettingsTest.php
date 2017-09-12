<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Block\Adminhtml\Widget\Instance\Edit\Tab;

use Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Settings;

/**
 * Test for \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Settings.
 */
class SettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    /**
     * @var \Magento\Framework\Data\FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formFactoryMock;

    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeLabelFactoryMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    protected function setUp()
    {
        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager $objectManager */
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->contextMock = $objectManager->getObject(
            \Magento\Backend\Block\Template\Context::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
            ]
        );
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->formFactoryMock = $this->getMockBuilder(\Magento\Framework\Data\FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeLabelFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Design\Theme\LabelFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->settings = new Settings(
            $this->contextMock,
            $this->registryMock,
            $this->formFactoryMock,
            $this->themeLabelFactoryMock,
            []
        );
    }

    public function testGetContinueUrl()
    {
        $this->urlBuilderMock->expects($this->once())->method('getUrl')->with(
            'adminhtml/*/*',
            [
                '_current' => true,
                'code' => '<%- data.code %>',
                'theme_id' => '<%- data.theme_id %>',
                '_escape_params' => false,
            ]
        )->willReturn('test.html');
        $this->assertSame('test.html', $this->settings->getContinueUrl());
    }
}
