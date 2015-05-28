<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Fieldset\Modules;

class DisableOutputTest extends \PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $testData = [
            'htmlId' => 'test_field_id',
            'label' => 'test_label',
            'elementHTML' => 'test_html',
            'legend' => 'test_legend',
            'comment' => 'test_comment',
        ];

        $testModuleList = [
            'testModuleWithConfigData',
            'testModuleNoConfigData',
        ];

        $configData = ['advanced/modules_disable_output/testModuleWithConfigData' => 'testModuleData'];

        $fieldMock = $this->getMock('Magento\Config\Block\System\Config\Form\Field', [], [], '', false, false, true);
        $layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false, false);
        $layoutMock->expects($this->once())
            ->method('getBlockSingleton')
            ->with('Magento\Config\Block\System\Config\Form\Field')
            ->willReturn($fieldMock);

        $groupMock = $this->getMock(
            'Magento\Config\Model\Config\Structure\Element\Group',
            [],
            [],
            '',
            false
        );
        $groupMock->expects($this->once())->method('getFieldsetCss')->willReturn('test_fieldset_css');

        $elementMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\Text',
            [
                'getHtmlId', 'getExpanded', 'getElements',
                'getLegend', 'getComment', 'addField', 'setRenderer', 'toHtml'
            ],
            [],
            '',
            false,
            false,
            true
        );
        $elementMock->expects(
            $this->any()
        )->method(
            'getHtmlId'
        )->willReturn(
            $testData['htmlId']
        );
        $elementMock->expects($this->any())->method('getExpanded')->willReturn(true);
        $elementMock->expects(
            $this->any()
        )->method(
            'getLegend'
        )->willReturn(
            $testData['legend']
        );
        $elementMock->expects(
            $this->any()
        )->method(
            'getComment'
        )->willReturn(
            $testData['comment']
        );
        $elementMock->expects($this->any())->method('addField')->willReturn($elementMock);
        $elementMock->expects($this->any())->method('setRenderer')->willReturn($elementMock);
        $elementMock->expects($this->any())->method('toHtml')->willReturn('test_element_html');

        $moduleListMock = $this->getMock(
            'Magento\Framework\Module\ModuleList',
            [],
            [],
            '',
            false,
            false
        );

        $moduleListMock->expects($this->any())->method('getNames')->willReturn(
                array_merge(['Magento_Backend'], $testModuleList)
        );

        $factory = $this->getMock('Magento\Framework\Data\Form\Element\Factory', [], [], '', false);
        $factoryColl = $this->getMock(
            'Magento\Framework\Data\Form\Element\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $formMock = $this->getMock('Magento\Framework\Data\Form\AbstractForm', [], [$factory, $factoryColl]);
        $formMock->expects($this->any())->method('getConfigValue')->willReturn('testConfigData');

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $object = $objectManager->getObject(
            'Magento\Config\Block\System\Config\Form\Fieldset\Modules\DisableOutput',
            [
                'moduleList' => $moduleListMock,
                'layout' => $layoutMock,
                'data' => [
                    'group'      => $groupMock,
                    'form'       => $formMock,
                    'config_data' => $configData,
                ],
            ]
        );

        $collection = $objectManager->getObject('Magento\Framework\Data\Form\Element\Collection');
        $elementMock->expects($this->any())->method('getElements')->willReturn($collection);

        $actualHtml = $object->render($elementMock);
        $this->assertContains('test_element_html', $actualHtml);
        $this->assertContains('test_field_id', $actualHtml);
        $this->assertContains('test_comment', $actualHtml);
    }
}
