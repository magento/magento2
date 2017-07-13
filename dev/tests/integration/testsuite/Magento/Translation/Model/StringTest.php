<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Model;

class StringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Translation\Model\StringUtils
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Translation\Model\StringUtils::class
        );
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(
            \Magento\Translation\Model\ResourceModel\StringUtils::class,
            $this->_model->getResource()
        );
    }

    public function testSetGetString()
    {
        $expectedString = __METHOD__;
        $this->_model->setString($expectedString);
        $actualString = $this->_model->getString();
        $this->assertEquals($expectedString, $actualString);
    }
}
