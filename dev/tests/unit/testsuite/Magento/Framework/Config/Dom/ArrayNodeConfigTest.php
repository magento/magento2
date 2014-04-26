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
namespace Magento\Framework\Config\Dom;

class ArrayNodeConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ArrayNodeConfig
     */
    protected $object;

    /**
     * @var NodePathMatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $nodePathMatcher;

    protected function setUp()
    {
        $this->nodePathMatcher = $this->getMock('\Magento\Framework\Config\Dom\NodePathMatcher');
        $this->object = new ArrayNodeConfig(
            $this->nodePathMatcher,
            array('/root/assoc/one' => 'name', '/root/assoc/two' => 'id', '/root/assoc/three' => 'key'),
            array('/root/numeric/one', '/root/numeric/two', '/root/numeric/three')
        );
    }

    public function testIsNumericArrayMatched()
    {
        $xpath = '/root/numeric[@attr="value"]/two';
        $this->nodePathMatcher->expects(
            $this->at(0)
        )->method(
            'match'
        )->with(
            '/root/numeric/one',
            $xpath
        )->will(
            $this->returnValue(false)
        );
        $this->nodePathMatcher->expects(
            $this->at(1)
        )->method(
            'match'
        )->with(
            '/root/numeric/two',
            $xpath
        )->will(
            $this->returnValue(true)
        );
        $this->assertTrue($this->object->isNumericArray($xpath));
    }

    public function testIsNumericArrayNotMatched()
    {
        $xpath = '/root/numeric[@attr="value"]/four';
        $this->nodePathMatcher->expects(
            $this->at(0)
        )->method(
            'match'
        )->with(
            '/root/numeric/one',
            $xpath
        )->will(
            $this->returnValue(false)
        );
        $this->nodePathMatcher->expects(
            $this->at(1)
        )->method(
            'match'
        )->with(
            '/root/numeric/two',
            $xpath
        )->will(
            $this->returnValue(false)
        );
        $this->nodePathMatcher->expects(
            $this->at(2)
        )->method(
            'match'
        )->with(
            '/root/numeric/three',
            $xpath
        )->will(
            $this->returnValue(false)
        );
        $this->assertFalse($this->object->isNumericArray($xpath));
    }

    public function testGetAssocArrayKeyAttributeMatched()
    {
        $xpath = '/root/assoc[@attr="value"]/two';
        $this->nodePathMatcher->expects(
            $this->at(0)
        )->method(
            'match'
        )->with(
            '/root/assoc/one',
            $xpath
        )->will(
            $this->returnValue(false)
        );
        $this->nodePathMatcher->expects(
            $this->at(1)
        )->method(
            'match'
        )->with(
            '/root/assoc/two',
            $xpath
        )->will(
            $this->returnValue(true)
        );
        $this->assertEquals('id', $this->object->getAssocArrayKeyAttribute($xpath));
    }

    public function testGetAssocArrayKeyAttributeNotMatched()
    {
        $xpath = '/root/assoc[@attr="value"]/four';
        $this->nodePathMatcher->expects(
            $this->at(0)
        )->method(
            'match'
        )->with(
            '/root/assoc/one',
            $xpath
        )->will(
            $this->returnValue(false)
        );
        $this->nodePathMatcher->expects(
            $this->at(1)
        )->method(
            'match'
        )->with(
            '/root/assoc/two',
            $xpath
        )->will(
            $this->returnValue(false)
        );
        $this->nodePathMatcher->expects(
            $this->at(2)
        )->method(
            'match'
        )->with(
            '/root/assoc/three',
            $xpath
        )->will(
            $this->returnValue(false)
        );
        $this->assertNull($this->object->getAssocArrayKeyAttribute($xpath));
    }
}
