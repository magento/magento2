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
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManager');
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
        return array(
            'css => css' => array(
                'css', 'css',
                array('Magento\Framework\View\Asset\PreProcessor\ModuleNotation'),
            ),
            'css => less (irrelevant)' => array(
                'css', 'less',
                array(),
            ),
            'less => css' => array(
                'less', 'css',
                array(
                    'Magento\Framework\Css\PreProcessor\Less',
                    'Magento\Framework\View\Asset\PreProcessor\ModuleNotation',
                ),
            ),
            'less => less' => array(
                'less', 'less',
                array(
                    'Magento\Framework\Less\PreProcessor\Instruction\MagentoImport',
                    'Magento\Framework\Less\PreProcessor\Instruction\Import',
                ),
            ),
            'txt => log (unsupported)' => array(
                'txt', 'log',
                array(),
            ),
        );
    }
}
