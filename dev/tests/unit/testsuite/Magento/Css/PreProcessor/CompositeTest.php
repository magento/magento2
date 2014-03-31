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
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $callMap = array();

    protected function setUp()
    {
        $this->preProcessorFactoryMock = $this->getMock(
            'Magento\View\Asset\PreProcessorFactory',
            array(),
            array(),
            '',
            false
        );
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @param array $preProcessors
     * @param array $createMap
     * @dataProvider processDataProvider
     */
    public function testProcess($preProcessors, $createMap)
    {
        $publisherFile = $this->getMock('Magento\View\Publisher\CssFile', array(), array(), '', false);
        $targetDir = $this->getMock('Magento\Filesystem\Directory\WriteInterface', array(), array(), '', false);

        foreach ($createMap as $className) {
            $this->callMap[$className] = $this->getMock($className, array(), array(), '', false);
            $this->callMap[$className]->expects(
                $this->once()
            )->method(
                'process'
            )->with(
                $this->equalTo($publisherFile),
                $this->equalTo($targetDir)
            )->will(
                $this->returnValue($publisherFile)
            );
        }

        $this->preProcessorFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnCallback(array($this, 'createProcessor'))
        );

        $this->composite = $this->objectManagerHelper->getObject(
            'Magento\Css\PreProcessor\Composite',
            array('preProcessorFactory' => $this->preProcessorFactoryMock, 'preProcessors' => $preProcessors)
        );

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
        return array(
            'one processor - LESS' => array(
                'preProcessors' => array('css_source_processor' => 'Magento\Css\PreProcessor\Less'),
                'createMap' => array('Magento\Css\PreProcessor\Less')
            ),
            'list of pre-processors' => array(
                'preProcessors' => array(
                    'css_source_processor' => 'Magento\Css\PreProcessor\Less',
                    'css_url_processor' => 'Magento\Css\PreProcessor\UrlResolver'
                ),
                'createMap' => array('Magento\Css\PreProcessor\Less', 'Magento\Css\PreProcessor\UrlResolver')
            ),
            'no processors' => array('preProcessors' => array(), 'createMap' => array())
        );
    }
}
