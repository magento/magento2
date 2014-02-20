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

namespace Magento\Css\PreProcessor;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Css\PreProcessor\Composite */
    protected $composite;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\View\Asset\PreProcessorFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $preProcessorFactoryMock;

    /**
     * @var array
     */
    protected $callMap = [];

    protected function setUp()
    {
        $this->preProcessorFactoryMock = $this->getMock('Magento\View\Asset\PreProcessorFactory', [], [], '', false);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @param array $params
     * @param array $preProcessors
     * @param array $createMap
     * @param string $expectedResult
     * @dataProvider processDataProvider
     */
    public function testProcess($params, $preProcessors, $createMap, $expectedResult)
    {
        $targetDir = $this->getMock($params['targetDirectory'], array(), array(), '', false);

        foreach ($createMap as $className) {
            $this->callMap[$className] = $this->getMock($className, array('process'), array(), '', false);
            $this->callMap[$className]->expects($this->once())
                ->method('process')
                ->with(
                    $this->equalTo($params['filePath']),
                    $this->equalTo($params['params']),
                    $this->equalTo($targetDir),
                    $this->equalTo($params['sourcePath'])
                )
                ->will($this->returnValue($expectedResult));
        }

        $this->preProcessorFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(array($this, 'createProcessor')));

        $this->composite = $this->objectManagerHelper->getObject(
            'Magento\Css\PreProcessor\Composite',
            [
                'preProcessorFactory' => $this->preProcessorFactoryMock,
                'preProcessors' => $preProcessors
            ]
        );

        $result = $this->composite->process(
            $params['filePath'],
            $params['params'],
            $targetDir,
            $params['sourcePath']
        );
        $this->assertEquals($expectedResult, $result);
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
            'one processor - LESS' => [
                'params' => [
                    'filePath' => '/some/file/path_one.css',
                    'params' => ['theme' => 'some_theme', 'area' => 'frontend'],
                    'targetDirectory' => 'Magento\Filesystem\Directory\WriteInterface',
                    'sourcePath' => 'result_source_path_one'
                ],
                'preProcessors' => [
                    'css_source_processor' => 'Magento\Css\PreProcessor\Less',
                ],
                'createMap' => [
                    'Magento\Css\PreProcessor\Less',
                ],
                'expectedResult' => 'result_source_path_one'
            ],
            'list of pre-processors' => [
                'params' => [
                    'filePath' => '/some/file/path.css',
                    'params' => ['theme' => 'some_theme', 'area' => 'frontend'],
                    'targetDirectory' => 'Magento\Filesystem\Directory\WriteInterface',
                    'sourcePath' => 'result_source_path_two'
                ],
                'preProcessors' => [
                    'css_source_processor' => 'Magento\Css\PreProcessor\Less',
                    'css_source_processor2' => 'Magento\Css\PreProcessor\Less2',
                ],
                'createMap' => [
                    'Magento\Css\PreProcessor\Less',
                    'Magento\Css\PreProcessor\Less2',
                ],
                'expectedResult' => 'result_source_path_two'
            ],
            'no result' => [
                'params' => [
                    'filePath' => '/some/file/path_other.css',
                    'params' => ['theme' => 'some_theme', 'area' => 'frontend'],
                    'targetDirectory' => 'Magento\Filesystem\Directory\WriteInterface',
                    'sourcePath' => null
                ],
                'preProcessors' => [
                    'css_source_processor' => 'Magento\Css\PreProcessor\Less',
                ],
                'createMap' => [
                    'Magento\Css\PreProcessor\Less',
                ],
                'expectedResult' => null
            ],
            'no processors' => [
                'params' => [
                    'filePath' => '/some/file/some_path.css',
                    'params' => ['theme' => 'some_theme', 'area' => 'frontend'],
                    'targetDirectory' => 'Magento\Filesystem\Directory\WriteInterface',
                    'sourcePath' => null
                ],
                'preProcessors' => [],
                'createMap' => [],
                'expectedResult' => null
            ],
        ];
    }
}
