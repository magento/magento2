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
 * @category    Varien
 * @package     Varien_Image
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Varien_Image_Adapter_Abstract.
 */
class Varien_Image_Adapter_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Varien_Image_Adapter_Abstract
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        $ioFile = $this->getMock('Varien_Io_File', array('mkdir'));
        $ioFile->expects($this->any())
            ->method('mkdir')
            ->will($this->returnValue(true));

        $data = array('io' => $ioFile);
        $this->_model = $this->getMockForAbstractClass('Varien_Image_Adapter_Abstract', array($data));
    }

    /**
     * Test _adaptResizeValues with null as a value one of parameters
     *
     * @dataProvider _adaptResizeValuesDataProvider
     */
    public function test_adaptResizeValues($width, $height, $expectedResult)
    {
        $method = new ReflectionMethod($this->_model, '_adaptResizeValues');
        $method->setAccessible(true);

        $result = $method->invoke($this->_model, $width, $height);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function _adaptResizeValuesDataProvider()
    {

        $expected = array(
            'src' => array(
                'x' => 0,
                'y' => 0
            ),
            'dst' => array(
                'x' => 0,
                'y' => 0,
                'width'  => 135,
                'height' => 135
            ),
            'frame' => array(
                'width'  => 135,
                'height' => 135
            )
        );

        return array(
            array(135, null, $expected),
            array(null, 135, $expected),
        );
    }

    /**
     * @dataProvider _prepareDestinationDataProvider
     */
    public function test_prepareDestination($destination, $newName, $expectedResult)
    {
        $property = new ReflectionProperty(get_class($this->_model), '_fileSrcPath');
        $property->setAccessible(true);
        $property->setValue($this->_model, '_fileSrcPath');

        $property = new ReflectionProperty(get_class($this->_model), '_fileSrcName');
        $property->setAccessible(true);
        $property->setValue($this->_model, '_fileSrcName');

        $method = new ReflectionMethod($this->_model, '_prepareDestination');
        $method->setAccessible(true);

        $result = $method->invoke($this->_model, $destination, $newName);

        $this->assertEquals($expectedResult, $result);
    }

    public function _prepareDestinationDataProvider()
    {
        return array(
            array(__DIR__, 'name.txt', __DIR__ . DIRECTORY_SEPARATOR . 'name.txt'),
            array(__DIR__ . DIRECTORY_SEPARATOR . 'name.txt', null, __DIR__ . DIRECTORY_SEPARATOR . 'name.txt'),
            array(null, 'name.txt', '_fileSrcPath' . DIRECTORY_SEPARATOR . 'name.txt'),
            array(null, null, '_fileSrcPath' . DIRECTORY_SEPARATOR . '_fileSrcName'),
        );
    }

}
