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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core layout utility
 */
namespace Magento\Core\Utility;

class Layout
{
    /**
     * @var \PHPUnit_Framework_TestCase
     */
    protected $_testCase;

    public function __construct(\PHPUnit_Framework_TestCase $testCase)
    {
        $this->_testCase = $testCase;
    }

    /**
     * Retrieve new layout update model instance with XML data from a fixture file
     *
     * @param string|array $layoutUpdatesFile
     * @return \Magento\Core\Model\Layout\Merge
     */
    public function getLayoutUpdateFromFixture($layoutUpdatesFile)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Core\Model\Layout\File\Factory $fileFactory */
        $fileFactory = $objectManager->get('Magento\Core\Model\Layout\File\Factory');
        $files = array();
        foreach ((array)$layoutUpdatesFile as $filename) {
            $files[] = $fileFactory->create($filename, 'Magento_Core');
        }
        $fileSource = $this->_testCase->getMockForAbstractClass('Magento\Core\Model\Layout\File\SourceInterface');
        $fileSource->expects(\PHPUnit_Framework_TestCase::any())
            ->method('getFiles')
            ->will(\PHPUnit_Framework_TestCase::returnValue($files));
        $cache = $this->_testCase->getMockForAbstractClass('Magento\Cache\FrontendInterface');
        return $objectManager->create(
            'Magento\View\Layout\ProcessorInterface', array('fileSource' => $fileSource, 'cache' => $cache)
        );
    }

    /**
     * Retrieve new layout model instance with layout updates from a fixture file
     *
     * @param string|array $layoutUpdatesFile
     * @param array $args
     * @return \Magento\Core\Model\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getLayoutFromFixture($layoutUpdatesFile, array $args = array())
    {
        $layout = $this->_testCase->getMock('Magento\Core\Model\Layout', array('getUpdate'), $args);
        $layoutUpdate = $this->getLayoutUpdateFromFixture($layoutUpdatesFile);
        $layoutUpdate->asSimplexml();
        $layout->expects(\PHPUnit_Framework_TestCase::any())
            ->method('getUpdate')
            ->will(\PHPUnit_Framework_TestCase::returnValue($layoutUpdate));
        return $layout;
    }

    /**
     * Retrieve object that will be used for layout instantiation
     *
     * @return array
     */
    public function getLayoutDependencies()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        return array(
            'processorFactory'       => $objectManager->get('Magento\View\Layout\ProcessorFactory'),
            'themeFactory'       => $objectManager->get('Magento\Core\Model\Resource\Theme\CollectionFactory'),
            'logger'             => $objectManager->get('Magento\Core\Model\Logger'),
            'eventManager'       => $objectManager->get('Magento\Event\ManagerInterface'),
            'factoryHelper'      => $objectManager->get('Magento\Core\Model\Factory\Helper'),
            'coreData'           => $objectManager->get('Magento\Core\Helper\Data'),
            'design'             => $objectManager->get('Magento\View\DesignInterface'),
            'blockFactory'       => $objectManager->create('Magento\Core\Model\BlockFactory', array()),
            'structure'          => $objectManager->create('Magento\Data\Structure', array()),
            'argumentProcessor'  => $objectManager->create('Magento\Core\Model\Layout\Argument\Processor', array()),
            'scheduledStructure' => $objectManager->create('Magento\Core\Model\Layout\ScheduledStructure', array()),
            'dataServiceGraph'   => $objectManager->create('Magento\Core\Model\DataService\Graph', array()),
            'coreStoreConfig'    => $objectManager->create('Magento\Core\Model\Store\Config'),
        );
    }
}
