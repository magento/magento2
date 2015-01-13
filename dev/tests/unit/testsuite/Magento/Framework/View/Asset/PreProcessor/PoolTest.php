<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

class PoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\PreProcessor\Pool
     */
    protected $factory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->factory = new Pool($this->objectManager);
    }

    /**
     * @param string $sourceContentType
     * @param string $targetContentType
     * @param array $expectedResult
     *
     * @dataProvider getPreProcessorsDataProvider
     */
    public function testGetPreProcessors($sourceContentType, $targetContentType, array $expectedResult)
    {
        // Make the object manager to return strings for simplicity of mocking
        $this->objectManager->expects($this->any())
            ->method('get')
            ->with($this->anything())
            ->will($this->returnArgument(0));
        $this->assertSame($expectedResult, $this->factory->getPreProcessors($sourceContentType, $targetContentType));
    }

    public function getPreProcessorsDataProvider()
    {
        return [
            'css => css' => [
                'css', 'css',
                ['Magento\Framework\View\Asset\PreProcessor\ModuleNotation'],
            ],
            'css => less (irrelevant)' => [
                'css', 'less',
                [],
            ],
            'less => css' => [
                'less', 'css',
                [
                    'Magento\Framework\Css\PreProcessor\Less',
                    'Magento\Framework\View\Asset\PreProcessor\ModuleNotation',
                ],
            ],
            'less => less' => [
                'less', 'less',
                [
                    'Magento\Framework\Less\PreProcessor\Instruction\MagentoImport',
                    'Magento\Framework\Less\PreProcessor\Instruction\Import',
                ],
            ],
            'txt => log (unsupported)' => [
                'txt', 'log',
                [],
            ],
        ];
    }
}
