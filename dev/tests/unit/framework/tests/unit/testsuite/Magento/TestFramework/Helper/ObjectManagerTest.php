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
namespace Magento\TestFramework\Helper;

class ObjectManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * List of block default dependencies
     *
     * @var array
     */
    protected $_blockDependencies = array(
        'request' => 'Magento\Framework\App\RequestInterface',
        'layout' => 'Magento\Framework\View\LayoutInterface',
        'eventManager' => 'Magento\Framework\Event\ManagerInterface',
        'translator' => 'Magento\Framework\TranslateInterface',
        'cache' => 'Magento\Framework\App\CacheInterface',
        'design' => 'Magento\Framework\View\DesignInterface',
        'session' => 'Magento\Framework\Session\SessionManagerInterface',
        'scopeConfig' => 'Magento\Framework\App\Config\ScopeConfigInterface'
    );

    /**
     * List of model default dependencies
     *
     * @var array
     */
    protected $_modelDependencies = array(
        'eventManager' => 'Magento\Framework\Event\ManagerInterface',
        'cacheManager' => 'Magento\Framework\App\CacheInterface',
        'resource' => 'Magento\Framework\Model\Resource\AbstractResource',
        'resourceCollection' => 'Magento\Framework\Data\Collection\Db'
    );

    /**
     * @covers \Magento\TestFramework\TestCase\ObjectManager::getBlock
     */
    public function testGetBlock()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var $template \Magento\Framework\View\Element\Template */
        $template = $objectManager->getObject('Magento\Framework\View\Element\Template');
        $this->assertInstanceOf('Magento\Framework\View\Element\Template', $template);
        foreach ($this->_blockDependencies as $propertyName => $propertyType) {
            $this->assertAttributeInstanceOf($propertyType, '_' . $propertyName, $template);
        }

        $area = 'frontend';
        /** @var $appStateMock \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject */
        $appStateMock = $this->getMock('Magento\Framework\App\State', array('getAreaCode'), array(), '', false);
        $appStateMock->expects($this->once())->method('getAreaCode')->will($this->returnValue($area));

        $context = $objectManager->getObject('Magento\Framework\View\Element\Template\Context');
        $appStateProperty = new \ReflectionProperty('Magento\Framework\View\Element\Template\Context', '_appState');
        $appStateProperty->setAccessible(true);
        $appStateProperty->setValue($context, $appStateMock);

        /** @var $template \Magento\Framework\View\Element\Template */
        $template = $objectManager->getObject('Magento\Framework\View\Element\Template', array('context' => $context));
        $this->assertEquals($area, $template->getArea());
    }

    /**
     * @covers \Magento\TestFramework\ObjectManager::getModel
     */
    public function testGetModel()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var $model \Magento\Framework\App\Config\ValueInterface */
        $model = $objectManager->getObject('Magento\Framework\App\Config\Value');
        $this->assertInstanceOf('Magento\Framework\App\Config\Value', $model);
        foreach ($this->_modelDependencies as $propertyName => $propertyType) {
            $this->assertAttributeInstanceOf($propertyType, '_' . $propertyName, $model);
        }

        /** @var $resourceMock \Magento\Core\Model\Resource\Resource */
        $resourceMock = $this->getMock(
            'Magento\Install\Model\Resource\Resource',
            array('_getReadAdapter', 'getIdFieldName', '__sleep', '__wakeup'),
            array(),
            '',
            false
        );
        $resourceMock->expects($this->once())->method('_getReadAdapter')->will($this->returnValue(false));
        $resourceMock->expects($this->any())->method('getIdFieldName')->will($this->returnValue('id'));
        $arguments = array('resource' => $resourceMock);
        $model = $objectManager->getObject('Magento\Framework\App\Config\Value', $arguments);
        $this->assertFalse($model->getResource()->getDataVersion('test'));
    }
}
