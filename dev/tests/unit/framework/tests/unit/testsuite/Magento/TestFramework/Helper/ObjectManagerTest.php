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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\TestFramework\Helper;

class ObjectManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * List of block default dependencies
     *
     * @var array
     */
    protected $_blockDependencies = array(
        'request'         => 'Magento\App\RequestInterface',
        'layout'          => 'Magento\View\LayoutInterface',
        'eventManager'    => 'Magento\Event\ManagerInterface',
        'translator'      => 'Magento\Core\Model\Translate',
        'cache'           => 'Magento\Core\Model\CacheInterface',
        'design'          => 'Magento\View\DesignInterface',
        'session'         => 'Magento\Core\Model\Session',
        'storeConfig'     => 'Magento\Core\Model\Store\Config',
        'frontController' => 'Magento\App\FrontController'
    );

    /**
     * List of model default dependencies
     *
     * @var array
     */
    protected $_modelDependencies = array(
        'eventDispatcher'    => 'Magento\Event\ManagerInterface',
        'cacheManager'       => 'Magento\Core\Model\CacheInterface',
        'resource'           => 'Magento\Core\Model\Resource\AbstractResource',
        'resourceCollection' => 'Magento\Data\Collection\Db'
    );

    /**
     * @covers \Magento\TestFramework\TestCase\ObjectManager::getBlock
     */
    public function testGetBlock()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var $template \Magento\Core\Block\Template */
        $template = $objectManager->getObject('Magento\Core\Block\Template');
        $this->assertInstanceOf('Magento\Core\Block\Template', $template);
        foreach ($this->_blockDependencies as $propertyName => $propertyType) {
            $this->assertAttributeInstanceOf($propertyType, '_' . $propertyName, $template);
        }

        $area = 'frontend';
        /** @var $layoutMock \Magento\Core\Model\Layout */
        $layoutMock = $this->getMockBuilder('Magento\View\LayoutInterface')->getMockForAbstractClass();
        $layoutMock->expects($this->once())
            ->method('getArea')
            ->will($this->returnValue($area));

        $arguments = array('layout' => $layoutMock);
        /** @var $template \Magento\Core\Block\Template */
        $template = $objectManager->getObject('Magento\Core\Block\Template', $arguments);
        $this->assertEquals($area, $template->getArea());
    }

    /**
     * @covers \Magento\TestFramework\ObjectManager::getModel
     */
    public function testGetModel()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var $model \Magento\Core\Model\Config\Value */
        $model = $objectManager->getObject('Magento\Core\Model\Config\Value');
        $this->assertInstanceOf('Magento\Core\Model\Config\Value', $model);
        foreach ($this->_modelDependencies as $propertyName => $propertyType) {
            $this->assertAttributeInstanceOf($propertyType, '_' . $propertyName, $model);
        }

        /** @var $resourceMock \Magento\Core\Model\Resource\Resource */
        $resourceMock = $this->getMock(
            'Magento\Core\Model\Resource\Resource',
            array('_getReadAdapter', 'getIdFieldName', '__sleep', '__wakeup'),
            array(),
            '',
            false
        );
        $resourceMock->expects($this->once())
            ->method('_getReadAdapter')
            ->will($this->returnValue(false));
        $resourceMock->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('id'));
        $arguments = array('resource' => $resourceMock);
        $model = $objectManager->getObject('Magento\Core\Model\Config\Value', $arguments);
        $this->assertFalse($model->getResource()->getDataVersion('test'));
    }
}
