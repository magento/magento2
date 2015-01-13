<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons;

class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested block
     *
     * @var \Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons\Save
     */
    protected $_block;

    /**
     * @var string
     */
    protected $_url = 'http://some.url.com';

    protected function setUp()
    {
        /** @var $escaper \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject */
        $escaper = $this->getMockBuilder(
            'Magento\Framework\Escaper'
        )->disableOriginalConstructor()->setMethods(
            ['escapeHtml']
        )->getMock();
        $escaper->expects($this->any())->method('escapeHtml')->will($this->returnArgument(0));

        /** @var $urlBuilder \Magento\Core\Model\Url|\PHPUnit_Framework_MockObject_MockObject */
        $urlBuilder = $this->getMock('Magento\Framework\Url', ['getUrl'], [], '', false);
        $urlBuilder->expects($this->any())->method('getUrl')->will($this->returnValue($this->_url));

        $context = $this->getMockBuilder(
            'Magento\Backend\Block\Template\Context'
        )->disableOriginalConstructor()->setMethods(
            ['getEscaper', 'getUrlBuilder']
        )->getMock();
        $context->expects($this->any())->method('getEscaper')->will($this->returnValue($escaper));
        $context->expects($this->any())->method('getUrlBuilder')->will($this->returnValue($urlBuilder));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_block = $objectManager->getObject(
            'Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\Buttons\Save',
            ['context' => $context]
        );
    }

    /**
     * @param \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject $theme
     * @param string $expected
     * @param array $expectedOptions
     * @dataProvider initDataProvider
     */
    public function testInit($theme, $expected, $expectedOptions)
    {
        $this->_block->setTheme($theme);
        $this->_block->init();
        $data = $this->_block->getData();
        $options = $this->_block->getOptions();
        $mainAction = json_decode($data['data_attribute']['mage-init'], true);

        if ($mainAction['button']['eventData']['confirm'] !== false) {
            $this->assertNotEmpty($mainAction['button']['eventData']['confirm']['message']);
            $this->assertNotEmpty($mainAction['button']['eventData']['confirm']['title']);
        }

        $mainAction['button']['eventData']['confirm'] = [];

        $this->assertEquals($expected, $mainAction);
        foreach ($options as $option) {
            $action = json_decode($option['data_attribute']['mage-init'], true);

            $this->assertNotEmpty($action['button']['eventData']['confirm']['message']);
            $this->assertNotEmpty($action['button']['eventData']['confirm']['title']);
            $action['button']['eventData']['confirm'] = [];

            $isFound = false;
            foreach ($expectedOptions as $expectedOption) {
                try {
                    $this->assertEquals($expectedOption, $action);
                    $isFound = true;
                } catch (\Exception $e) {
                    //do nothing
                }
            }

            if (!$isFound) {
                $this->fail(sprintf('Option [%s] is not found', $option['data_attribute']['mage-init']));
            }
        }
    }

    /**
     * @return array
     */
    public function initDataProvider()
    {
        return [
            'Physical theme' => [
                $this->_getThemeMock(\Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL),
                [
                    'button' => [
                        'event' => 'assign',
                        'target' => 'body',
                        'eventData' => ['theme_id' => 123, 'confirm' => []],
                    ]
                ],
                [],
            ],
            'Virtual assigned theme' => [
                $this->_getThemeMock(\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL, true),
                [
                    'button' => [
                        'event' => 'save',
                        'target' => 'body',
                        'eventData' => ['theme_id' => 123, 'save_url' => $this->_url, 'confirm' => []],
                    ]
                ],
                [],
            ],
            'Virtual unassigned theme' => [
                $this->_getThemeMock(\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL, false),
                [
                    'button' => [
                        'event' => 'save',
                        'target' => 'body',
                        'eventData' => ['theme_id' => 123, 'save_url' => $this->_url, 'confirm' => []],
                    ]
                ],
                [
                    [
                        'button' => [
                            'event' => 'save',
                            'target' => 'body',
                            'eventData' => ['theme_id' => 123, 'save_url' => $this->_url, 'confirm' => []],
                        ],
                    ],
                    [
                        'button' => [
                            'event' => 'save-and-assign',
                            'target' => 'body',
                            'eventData' => ['theme_id' => 123, 'save_url' => $this->_url, 'confirm' => []],
                        ]
                    ]
                ],
            ]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid theme of a "2" type passed to save button block
     */
    public function testInitStaging()
    {
        // 1. Get theme mock
        $stagingTheme = $this->_getThemeMock(\Magento\Framework\View\Design\ThemeInterface::TYPE_STAGING);

        $block = $this->_block;

        $block->setTheme($stagingTheme);
        $block->init();
    }

    /**
     * @param int $type
     * @param null|bool $isAssigned
     * @return \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getThemeMock($type, $isAssigned = null)
    {
        $themeId = 123;

        if ($type == \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL) {
            $theme = $this->_getVirtualThemeMock($type, $isAssigned);
        } else {
            $theme = $this->getMock('Magento\Core\Model\Theme', ['__sleep', '__wakeup'], [], '', false);
        }

        $theme->setType($type);
        $theme->setId($themeId);

        return $theme;
    }

    /**
     * @param int $type
     * @param bool $isAssigned
     * @return \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getVirtualThemeMock($type, $isAssigned)
    {
        // 1. Get domain model
        /** @var $domainModel \Magento\Core\Model\Theme\Domain\Virtual|\PHPUnit_Framework_MockObject_MockObject */
        $domainModel = $this->getMock(
            'Magento\Core\Model\Theme\Domain\Virtual',
            ['isAssigned'],
            [],
            '',
            false
        );
        $domainModel->expects($this->any())->method('isAssigned')->will($this->returnValue($isAssigned));

        // 2. Get Theme mock
        /** @var $theme \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject */
        $theme = $this->getMock(
            'Magento\Core\Model\Theme',
            ['getDomainModel', '__sleep', '__wakeup'],
            [],
            '',
            false
        );
        $theme->expects($this->any())->method('getDomainModel')->with($type)->will($this->returnValue($domainModel));

        return $theme;
    }
}
