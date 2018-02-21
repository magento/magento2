<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\View\Test\Unit\Element;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Framework\Config\View;
use Magento\Framework\View\ConfigInterface;

class AbstractBlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractBlock
     */
    protected $block;

    /**
     * @return void
     */
    protected function setUp()
    {
        $contextMock = $this->getMock(Context::class, [], [], '', false);
        $this->block = $this->getMockForAbstractClass(
            AbstractBlock::class,
            ['context' => $contextMock]
        );
    }

    /**
     * @param string $expectedResult
     * @param string $nameInLayout
     * @param array $methodArguments
     * @dataProvider getUiIdDataProvider
     */
    public function testGetUiId($expectedResult, $nameInLayout, $methodArguments)
    {
        $this->block->setNameInLayout($nameInLayout);
        $this->assertEquals($expectedResult, call_user_func_array([$this->block, 'getUiId'], $methodArguments));
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

    /**
     * @return void
     */
    public function testGetVar()
    {
        $config = $this->getMock(View::class, ['getVarValue'], [], '', false);
        $module = uniqid();

        $config->expects($this->any())
            ->method('getVarValue')
            ->willReturnMap([
                ['Magento_Theme', 'v1', 'one'],
                [$module, 'v2', 'two']
            ]);

        $configManager = $this->getMock(ConfigInterface::class, [], [], '', false);
        $configManager->expects($this->exactly(2))->method('getViewConfig')->willReturn($config);

        /** @var $block AbstractBlock|\PHPUnit_Framework_MockObject_MockObject */
        $params = ['viewConfig' => $configManager];
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $block = $this->getMockForAbstractClass(
            AbstractBlock::class,
            $helper->getConstructArguments(AbstractBlock::class, $params)
        );
        $block->setData('module_name', 'Magento_Theme');

        $this->assertEquals('one', $block->getVar('v1'));
        $this->assertEquals('two', $block->getVar('v2', $module));
    }

    /**
     * @return void
     */
    public function testIsScopePrivate()
    {
        $this->assertFalse($this->block->isScopePrivate());
    }

    /**
     * @return void
     */
    public function testGetCacheKey()
    {
        $cacheKey = 'testKey';
        $this->block->setData('cache_key', $cacheKey);
        $this->assertEquals(AbstractBlock::CACHE_KEY_PREFIX . $cacheKey, $this->block->getCacheKey());
    }

    /**
     * @return void
     */
    public function testGetCacheKeyByName()
    {
        $nameInLayout = 'testBlock';
        $this->block->setNameInLayout($nameInLayout);
        $cacheKey = sha1($nameInLayout);
        $this->assertEquals(AbstractBlock::CACHE_KEY_PREFIX . $cacheKey, $this->block->getCacheKey());
    }
}
