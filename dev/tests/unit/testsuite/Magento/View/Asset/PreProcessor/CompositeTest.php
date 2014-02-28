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

namespace Magento\View\Asset\PreProcessor;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\View\Asset\PreProcessor\Composite
     */
    protected $composite;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\View\Asset\PreProcessorFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $preProcessorFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $callMap = [];

    protected function setUp()
    {
        $this->preProcessorFactoryMock = $this->getMock('Magento\View\Asset\PreProcessorFactory', [], [], '', false);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @param array $extension
     * @param array $preProcessorsConfig
     * @param array $createMap
     * @dataProvider processDataProvider
     */
    public function testProcess($extension, $preProcessorsConfig, $createMap)
    {
        $this->composite = $this->objectManagerHelper->getObject(
            'Magento\View\Asset\PreProcessor\Composite',
            [
                'preProcessorFactory' => $this->preProcessorFactoryMock,
                'preProcessorsConfig' => $preProcessorsConfig
            ]
        );

        $publisherFile = $this->getMock('Magento\View\Publisher\CssFile', [], [], '', false);
        $publisherFile->expects($this->once())
            ->method('getExtension')
            ->will($this->returnValue($extension));

        $targetDir = $this->getMock('Magento\Filesystem\Directory\WriteInterface', array(), array(), '', false);

        foreach ($createMap as $className => $isExpected) {
            $this->callMap[$className] = $this->getMock($className, array('process'), array(), '', false);

            if ($isExpected === 'expected') {
                $this->callMap[$className]->expects($this->once())
                    ->method('process')
                    ->with(
                        $this->equalTo($publisherFile),
                        $this->equalTo($targetDir)
                    )
                    ->will($this->returnValue($publisherFile));
            } else {
                $this->callMap[$className]->expects($this->never())->method('process');
            }
        }

        $this->preProcessorFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(array($this, 'createProcessor')));

        $this->assertEquals($publisherFile, $this->composite->process($publisherFile, $targetDir));
    }

    /**
     * Create pre-processor callback
     *
     * @param string $className
     * @return \Magento\View\Asset\PreProcessor\PreProcessorInterface[]
     */
    public function createProcessor($className)
    {
        return $this->callMap[$className];
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'list of processors for css' => [
                'extension' => 'css',
                'preProcessorsConfig' => [
                    'css_preprocessor' => [
                        'class' => 'Magento\Css\PreProcessor\Composite',
                        'asset_type' => 'css'
                    ],
                    'css_preprocessor2' => [
                        'class' => 'Magento\Css\PreProcessor\Composite2',
                        'asset_type' => 'css'
                    ],
                ],
                'createMap' => [
                    'Magento\Css\PreProcessor\Composite' => 'expected',
                    'Magento\Css\PreProcessor\Composite2' => 'expected'
                ],
            ],
            'one processor for css' => [
                'extension' => 'css',
                'preProcessorsConfig' => [
                    'css_preprocessor' => [
                        'class' => 'Magento\Css\PreProcessor\Composite',
                        'asset_type' => 'css'
                    ],
                ],
                'createMap' => [
                    'Magento\Css\PreProcessor\Composite' => 'expected',
                ],
            ],
            'no processors' => [
                'extension' => 'css',
                'preProcessorsConfig' => [],
                'createMap' => [],
            ],
            'one processor for xyz' => [
                'extension' => 'css',
                'preProcessorsConfig' => [
                    'css_preprocessor' => [
                        'class' => 'Magento\Css\PreProcessor\Composite',
                        'asset_type' => 'xyz'
                    ],
                ],
                'createMap' => [
                    'Magento\Css\PreProcessor\Composite' => 'not expected',
                ],
            ],

        ];
    }
}
