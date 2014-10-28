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
        $block = $this->getMockForAbstractClass('Magento\Framework\View\Element\AbstractBlock', array(), '', false);
        $block->setNameInLayout($nameInLayout);

        $this->assertEquals($expectedResult, call_user_func_array(array($block, 'getUiId'), $methodArguments));
    }

    /**
     * @return array
     */
    public function getUiIdDataProvider()
    {
        return array(
            array(' data-ui-id="" ', null, array()),
            array(' data-ui-id="block" ', 'block', array()),
            array(' data-ui-id="block" ', 'block---', array()),
            array(' data-ui-id="block" ', '--block', array()),
            array(' data-ui-id="bl-ock" ', '--bl--ock---', array()),
            array(' data-ui-id="bl-ock" ', '--bL--Ock---', array()),
            array(' data-ui-id="b-l-o-c-k" ', '--b!@#$%^&**()L--O;:...c<_>k---', array()),
            array(
                ' data-ui-id="a0b1c2d3e4f5g6h7-i8-j9k0l1m2n-3o4p5q6r7-s8t9u0v1w2z3y4x5" ',
                'a0b1c2d3e4f5g6h7',
                array('i8-j9k0l1m2n-3o4p5q6r7', 's8t9u0v1w2z3y4x5')
            ),
            array(
                ' data-ui-id="capsed-block-name-cap-ed-param1-caps2-but-ton" ',
                'CaPSed BLOCK NAME',
                array('cAp$Ed PaRaM1', 'caPs2', 'bUT-TOn')
            ),
            array(
                ' data-ui-id="capsed-block-name-cap-ed-param1-caps2-but-ton-but-ton" ',
                'CaPSed BLOCK NAME',
                array('cAp$Ed PaRaM1', 'caPs2', 'bUT-TOn', 'bUT-TOn')
            ),
            array(' data-ui-id="block-0-1-2-3-4" ', '!block!', range(0, 5))
        );
    }

    public function testGetVar()
    {
        $this->markTestIncomplete('MAGETWO-11727');
        $config = $this->getMock('Magento\Framework\Config\View', array('getVarValue'), array(), '', false);
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

        $configManager = $this->getMock('Magento\Framework\View\ConfigInterface', array(), array(), '', false);
        $configManager->expects($this->exactly(2))->method('getViewConfig')->will($this->returnValue($config));

        /** @var $block \Magento\Framework\View\Element\AbstractBlock|\PHPUnit_Framework_MockObject_MockObject */
        $params = array('viewConfig' => $configManager);
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
        $contextMock = $this->getMock('Magento\Framework\View\Element\Context', array(), array(), '', false);
        $block = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\AbstractBlock',
            array('context' => $contextMock)
        );
        $this->assertEquals(false, $block->isScopePrivate());
    }
}
