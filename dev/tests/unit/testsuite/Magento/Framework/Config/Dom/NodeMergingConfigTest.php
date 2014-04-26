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

class NodeMergingConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NodeMergingConfig
     */
    protected $object;

    /**
     * @var NodePathMatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $nodePathMatcher;

    protected function setUp()
    {
        $this->nodePathMatcher = $this->getMock('\Magento\Framework\Config\Dom\NodePathMatcher');
        $this->object = new NodeMergingConfig(
            $this->nodePathMatcher,
            array('/root/one' => 'name', '/root/two' => 'id', '/root/three' => 'key')
        );
    }

    public function testGetIdAttributeMatched()
    {
        $xpath = '/root/two[@attr="value"]';
        $this->nodePathMatcher->expects(
            $this->at(0)
        )->method(
            'match'
        )->with(
            '/root/one',
            $xpath
        )->will(
            $this->returnValue(false)
        );
        $this->nodePathMatcher->expects(
            $this->at(1)
        )->method(
            'match'
        )->with(
            '/root/two',
            $xpath
        )->will(
            $this->returnValue(true)
        );
        $this->assertEquals('id', $this->object->getIdAttribute($xpath));
    }

    public function testGetIdAttributeNotMatched()
    {
        $xpath = '/root/four[@attr="value"]';
        $this->nodePathMatcher->expects(
            $this->at(0)
        )->method(
            'match'
        )->with(
            '/root/one',
            $xpath
        )->will(
            $this->returnValue(false)
        );
        $this->nodePathMatcher->expects(
            $this->at(1)
        )->method(
            'match'
        )->with(
            '/root/two',
            $xpath
        )->will(
            $this->returnValue(false)
        );
        $this->nodePathMatcher->expects(
            $this->at(2)
        )->method(
            'match'
        )->with(
            '/root/three',
            $xpath
        )->will(
            $this->returnValue(false)
        );
        $this->assertNull($this->object->getIdAttribute($xpath));
    }
}
