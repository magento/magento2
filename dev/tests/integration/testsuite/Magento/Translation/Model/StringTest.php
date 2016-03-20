<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Model;

class StringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Translation\Model\StringUtils
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Translation\Model\StringUtils'
        );
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('Magento\Translation\Model\ResourceModel\StringUtils', $this->_model->getResource());
    }

    public function testSetGetString()
    {
        $expectedString = __METHOD__;
        $this->_model->setString($expectedString);
        $actualString = $this->_model->getString();
        $this->assertEquals($expectedString, $actualString);
    }
}
