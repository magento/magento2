<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Model;

class StringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Translation\Model\String
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Translation\Model\String'
        );
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('Magento\Translation\Model\Resource\String', $this->_model->getResource());
    }

    public function testSetGetString()
    {
        $expectedString = __METHOD__;
        $this->_model->setString($expectedString);
        $actualString = $this->_model->getString();
        $this->assertEquals($expectedString, $actualString);
    }
}
