<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\TemplateEngine\Plugin;

class DebugHintsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DebugHints
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_coreData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_coreData = $this->getMock('Magento\Core\Helper\Data', [], [], '', false);
        $this->subjectMock = $this->getMock(
            'Magento\Framework\View\TemplateEngineFactory',
            [],
            [],
            '',
            false
        );
        $this->_model = new DebugHints($this->_objectManager, $this->_scopeConfig, $this->_coreData);
    }

    /**
     * @param bool $showBlockHints
     * @dataProvider afterCreateActiveDataProvider
     */
    public function testAfterCreateActive($showBlockHints)
    {
        $this->_coreData->expects($this->once())->method('isDevAllowed')->will($this->returnValue(true));
        $this->_setupConfigFixture(true, $showBlockHints);
        $engine = $this->getMock('Magento\Framework\View\TemplateEngineInterface');
        $engineDecorated = $this->getMock('Magento\Framework\View\TemplateEngineInterface');
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Core\Model\TemplateEngine\Decorator\DebugHints',
            $this->identicalTo(['subject' => $engine, 'showBlockHints' => $showBlockHints])
        )->will(
            $this->returnValue($engineDecorated)
        );
        $this->assertEquals($engineDecorated, $this->_model->afterCreate($this->subjectMock, $engine));
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
        $this->_coreData->expects($this->any())->method('isDevAllowed')->will($this->returnValue($isDevAllowed));
        $this->_setupConfigFixture($showTemplateHints, true);
        $this->_objectManager->expects($this->never())->method('create');
        $engine = $this->getMock('Magento\Framework\View\TemplateEngineInterface', [], [], '', false);
        $this->assertSame($engine, $this->_model->afterCreate($this->subjectMock, $engine));
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
        $this->_scopeConfig->expects(
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
