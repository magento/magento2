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

/**
 * Test class for \Magento\Framework\Image\Adapter\AbstractAdapter.
 */
namespace Magento\Framework\Image\Adapter;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Image\Adapter\AbstractAdapter
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject |\Magento\Framework\Filesystem\Directory\Write
     */
    protected $directoryWriteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject |\Magento\Framework\Filesystem
     */
    protected $filesystemMock;

    protected function setUp()
    {
        $this->directoryWriteMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            array(),
            array(),
            '',
            false
        );
        $this->filesystemMock = $this->getMock(
            'Magento\Framework\App\Filesystem',
            array('getDirectoryWrite', 'createDirectory'),
            array(),
            '',
            false
        );
        $this->filesystemMock->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->will(
            $this->returnValue($this->directoryWriteMock)
        );

        $this->_model = $this->getMockForAbstractClass(
            'Magento\Framework\Image\Adapter\AbstractAdapter',
            array($this->filesystemMock)
        );
    }

    protected function tearDown()
    {
        $this->directoryWriteMock = null;
        $this->_model = null;
        $this->filesystemMock = null;
    }

    /**
     * Test adaptResizeValues with null as a value one of parameters
     *
     * @dataProvider adaptResizeValuesDataProvider
     */
    public function testAdaptResizeValues($width, $height, $expectedResult)
    {
        $method = new \ReflectionMethod($this->_model, '_adaptResizeValues');
        $method->setAccessible(true);

        $result = $method->invoke($this->_model, $width, $height);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function adaptResizeValuesDataProvider()
    {

        $expected = array(
            'src' => array('x' => 0, 'y' => 0),
            'dst' => array('x' => 0, 'y' => 0, 'width' => 135, 'height' => 135),
            'frame' => array('width' => 135, 'height' => 135)
        );

        return array(array(135, null, $expected), array(null, 135, $expected));
    }

    /**
     * @dataProvider prepareDestinationDataProvider
     */
    public function testPrepareDestination($destination, $newName, $expectedResult)
    {
        $property = new \ReflectionProperty(get_class($this->_model), '_fileSrcPath');
        $property->setAccessible(true);
        $property->setValue($this->_model, '_fileSrcPath');

        $property = new \ReflectionProperty(get_class($this->_model), '_fileSrcName');
        $property->setAccessible(true);
        $property->setValue($this->_model, '_fileSrcName');

        $method = new \ReflectionMethod($this->_model, '_prepareDestination');
        $method->setAccessible(true);

        $result = $method->invoke($this->_model, $destination, $newName);

        $this->assertEquals($expectedResult, $result);
    }

    public function prepareDestinationDataProvider()
    {
        return array(
            array(__DIR__, 'name.txt', __DIR__ . '/name.txt'),
            array(__DIR__ . '/name.txt', null, __DIR__ . '/name.txt'),
            array(null, 'name.txt', '_fileSrcPath' . '/name.txt'),
            array(null, null, '_fileSrcPath' . '/_fileSrcName')
        );
    }
}
