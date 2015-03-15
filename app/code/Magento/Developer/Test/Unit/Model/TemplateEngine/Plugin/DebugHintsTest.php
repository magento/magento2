<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\TemplateEngine\Plugin;

use Magento\Developer\Model\TemplateEngine\Plugin\DebugHints;

class DebugHintsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DebugHints
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $devHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->devHelperMock = $this->getMock('Magento\Developer\Helper\Data', [], [], '', false);
        $this->subjectMock = $this->getMock(
            'Magento\Framework\View\TemplateEngineFactory',
            [],
            [],
            '',
            false
        );
        $this->model = new \Magento\Developer\Model\TemplateEngine\Plugin\DebugHints($this->objectManagerMock, $this->scopeConfigMock, $this->devHelperMock);
    }

    /**
     * @param bool $showBlockHints
     * @dataProvider afterCreateActiveDataProvider
     */
    public function testAfterCreateActive($showBlockHints)
    {
        $this->devHelperMock->expects($this->once())->method('isDevAllowed')->will($this->returnValue(true));
        $this->_setupConfigFixture(true, $showBlockHints);
        $engine = $this->getMock('Magento\Framework\View\TemplateEngineInterface');
        $engineDecorated = $this->getMock('Magento\Framework\View\TemplateEngineInterface');
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Developer\Model\TemplateEngine\Decorator\DebugHints',
            $this->identicalTo(['subject' => $engine, 'showBlockHints' => $showBlockHints])
        )->will(
            $this->returnValue($engineDecorated)
        );
        $this->assertEquals($engineDecorated, $this->model->afterCreate($this->subjectMock, $engine));
    }

    public function afterCreateActiveDataProvider()
    {
        return ['block hints disabled' => [false], 'block hints enabled' => [true]];
    }

    /**
     * @param bool $isDevAllowed
     * @param bool $showTemplateHints
     * @dataProvider afterCreateInactiveDataProvider
     */
    public function testAfterCreateInactive($isDevAllowed, $showTemplateHints)
    {
        $this->devHelperMock->expects($this->any())->method('isDevAllowed')->will($this->returnValue($isDevAllowed));
        $this->_setupConfigFixture($showTemplateHints, true);
        $this->objectManagerMock->expects($this->never())->method('create');
        $engine = $this->getMock('Magento\Framework\View\TemplateEngineInterface', [], [], '', false);
        $this->assertSame($engine, $this->model->afterCreate($this->subjectMock, $engine));
    }

    public function afterCreateInactiveDataProvider()
    {
        return [
            'dev disabled, template hints disabled' => [false, false],
            'dev disabled, template hints enabled' => [false, true],
            'dev enabled, template hints disabled' => [true, false]
        ];
    }

    /**
     * Setup fixture values for store config
     *
     * @param bool $showTemplateHints
     * @param bool $showBlockHints
     */
    protected function _setupConfigFixture($showTemplateHints, $showBlockHints)
    {
        $this->scopeConfigMock->expects(
            $this->atLeastOnce()
        )->method(
            'getValue'
        )->will(
            $this->returnValueMap(
                [
                    [
                        DebugHints::XML_PATH_DEBUG_TEMPLATE_HINTS,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        null,
                        $showTemplateHints,
                    ],
                    [
                        DebugHints::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        null,
                        $showBlockHints
                    ],
                ]
            )
        );
    }
}
