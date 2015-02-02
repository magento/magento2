<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

class AbstractBlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $expectedResult
     * @param string $nameInLayout
     * @param array $methodArguments
     * @dataProvider getUiIdDataProvider
     */
    public function testGetUiId($expectedResult, $nameInLayout, $methodArguments)
    {
        /** @var $block \Magento\Framework\View\Element\AbstractBlock|\PHPUnit_Framework_MockObject_MockObject */
        $block = $this->getMockForAbstractClass('Magento\Framework\View\Element\AbstractBlock', [], '', false);
        $block->setNameInLayout($nameInLayout);

        $this->assertEquals($expectedResult, call_user_func_array([$block, 'getUiId'], $methodArguments));
    }

    /**
     * @return array
     */
    public function getUiIdDataProvider()
    {
        return [
            [' data-ui-id="" ', null, []],
            [' data-ui-id="block" ', 'block', []],
            [' data-ui-id="block" ', 'block---', []],
            [' data-ui-id="block" ', '--block', []],
            [' data-ui-id="bl-ock" ', '--bl--ock---', []],
            [' data-ui-id="bl-ock" ', '--bL--Ock---', []],
            [' data-ui-id="b-l-o-c-k" ', '--b!@#$%^&**()L--O;:...c<_>k---', []],
            [
                ' data-ui-id="a0b1c2d3e4f5g6h7-i8-j9k0l1m2n-3o4p5q6r7-s8t9u0v1w2z3y4x5" ',
                'a0b1c2d3e4f5g6h7',
                ['i8-j9k0l1m2n-3o4p5q6r7', 's8t9u0v1w2z3y4x5']
            ],
            [
                ' data-ui-id="capsed-block-name-cap-ed-param1-caps2-but-ton" ',
                'CaPSed BLOCK NAME',
                ['cAp$Ed PaRaM1', 'caPs2', 'bUT-TOn']
            ],
            [
                ' data-ui-id="capsed-block-name-cap-ed-param1-caps2-but-ton-but-ton" ',
                'CaPSed BLOCK NAME',
                ['cAp$Ed PaRaM1', 'caPs2', 'bUT-TOn', 'bUT-TOn']
            ],
            [' data-ui-id="block-0-1-2-3-4" ', '!block!', range(0, 5)]
        ];
    }

    public function testGetVar()
    {
        $this->markTestIncomplete('MAGETWO-11727');
        $config = $this->getMock('Magento\Framework\Config\View', ['getVarValue'], [], '', false);
        $module = uniqid();
        $config->expects(
            $this->at(0)
        )->method(
            'getVarValue'
        )->with(
            'Magento_Core',
            'v1'
        )->will(
            $this->returnValue('one')
        );
        $config->expects($this->at(1))->method('getVarValue')->with($module, 'v2')->will($this->returnValue('two'));

        $configManager = $this->getMock('Magento\Framework\View\ConfigInterface', [], [], '', false);
        $configManager->expects($this->exactly(2))->method('getViewConfig')->will($this->returnValue($config));

        /** @var $block \Magento\Framework\View\Element\AbstractBlock|\PHPUnit_Framework_MockObject_MockObject */
        $params = ['viewConfig' => $configManager];
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $block = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\AbstractBlock',
            $helper->getConstructArguments('Magento\Framework\View\Element\AbstractBlock', $params),
            uniqid('Magento\\Core\\Block\\AbstractBlock\\')
        );

        $this->assertEquals('one', $block->getVar('v1'));
        $this->assertEquals('two', $block->getVar('v2', $module));
    }

    public function testIsScopePrivate()
    {
        $contextMock = $this->getMock('Magento\Framework\View\Element\Context', [], [], '', false);
        $block = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\AbstractBlock',
            ['context' => $contextMock]
        );
        $this->assertEquals(false, $block->isScopePrivate());
    }
}
