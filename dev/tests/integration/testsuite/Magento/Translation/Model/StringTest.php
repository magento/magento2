<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Translation\Model;

class StringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Translation\Model\StringUtils
     */
    protected $_model;

    protected function setUp(): void
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
