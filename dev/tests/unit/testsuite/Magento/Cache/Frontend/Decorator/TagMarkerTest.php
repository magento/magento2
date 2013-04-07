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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Cache_Frontend_Decorator_TagMarkerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Cache_Frontend_Decorator_TagMarker
     */
    protected $_object;

    /**
     * @var Magento_Cache_FrontendInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_frontend;

    /**
     * @var string
     */
    protected $_tagForTests = 'custom_tag';

    public function setUp()
    {
        $this->_frontend = $this->getMock('Magento_Cache_FrontendInterface');
        $this->_object = new Magento_Cache_Frontend_Decorator_TagMarker($this->_frontend, $this->_tagForTests);
    }

    public function testGetTag()
    {
        $this->assertEquals($this->_tagForTests, $this->_object->getTag());
    }

    public function testSave()
    {
        $this->_frontend->expects($this->once())
            ->method('save')
            ->with('record_value', 'record_id', array('passed_tag', $this->_tagForTests), 111)
            ->will($this->returnValue(true));

        $result = $this->_object->save('record_value', 'record_id', array('passed_tag'), 111);
        $this->assertTrue($result);
    }
}
