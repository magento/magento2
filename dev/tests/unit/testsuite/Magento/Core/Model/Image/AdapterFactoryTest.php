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
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Image;

class AdapterFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object Manager Helper
     *
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManagerHelper;

    protected function setUp()
    {
        $this->_objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @dataProvider createDataProvider
     * @param string $adapter
     * @param string $class
     */
    public function testCreate($adapter, $class)
    {
        $imageAdapter = $this->getMock($class, array('checkDependencies'), array(), '', false);
        $imageAdapter->expects($this->any())
            ->method('checkDependencies')
            ->will($this->returnValue(null));
        $objectManagerMock = $this->getMock('Magento\ObjectManager');
        $objectManagerMock->expects($this->any())
           ->method('create')
           ->will($this->returnValue($imageAdapter));
        $this->_getModel(array(
            'objectManager' => $objectManagerMock,
        ))->create($adapter);

        $storeConfigMock = $this->getMock('Magento\Core\Model\Store\Config', array('getConfig'), array(), '', false);
        $storeConfigMock->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($adapter));

        $this->_getModel(array(
            'storeConfig' => $storeConfigMock,
            'objectManager' => $objectManagerMock,
        ))->create();
    }

    /**
     * @see self::testCreate()
     * @return array
     */
    public function createDataProvider()
    {
        return array(
            array(\Magento\Core\Model\Image\AdapterFactory::ADAPTER_GD2, 'Magento\Image\Adapter\Gd2'),
            array(\Magento\Core\Model\Image\AdapterFactory::ADAPTER_IM, 'Magento\Image\Adapter\ImageMagick'),
        );
    }

    /**
     * @covers \Magento\Core\Model\Image\AdapterFactory::create
     * @dataProvider invalidArgumentExceptionDataProvider
     * @expectedException \InvalidArgumentException
     * @param string $adapter
     */
    public function testInvalidArgumentException($adapter)
    {
        $this->_getModel()->create($adapter);
    }

    /**
     * @see self::testInvalidArgumentException()
     * @return array
     */
    public function invalidArgumentExceptionDataProvider()
    {
        return array(
            array(''),
            array('incorrect'),
        );
    }

    /**
     * @covers \Magento\Core\Model\Image\AdapterFactory::create
     * @expectedException \Magento\Core\Exception
     */
    public function testMageCoreException()
    {
        $objectManagerMock = $this->getMock('Magento\ObjectManager');
        $imageAdapter = $this->getMockForAbstractClass('Magento\Image\Adapter\AbstractAdapter');
        $imageAdapter->expects($this->any())
            ->method('checkDependencies')
            ->will($this->throwException(new \Exception));
        $objectManagerMock->expects($this->any())
           ->method('create')
           ->will($this->returnValue($imageAdapter));
        $this->_getModel(array(
            'objectManager' => $objectManagerMock,
        ))->create();
    }

    /**
     * @param array $mockObjects
     * @return \Magento\Core\Model\Image\AdapterFactory
     */
    protected function _getModel(array $mockObjects = array())
    {
        return $this->_objectManagerHelper->getObject(
            'Magento\Core\Model\Image\AdapterFactory',
            $mockObjects
        );
    }
}
