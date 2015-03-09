<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n\Test\Unit\Dictionary\Writer\Csv;

class StdoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var resource
     */
    protected $_handler;

    protected function setUp()
    {
        $this->_handler = STDOUT;
    }

    public function testThatHandlerIsRight()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Tools\I18n\Dictionary\Writer\Csv $writer */
        $writer = $objectManagerHelper->getObject('Magento\Tools\I18n\Dictionary\Writer\Csv\Stdo');

        $this->assertAttributeEquals($this->_handler, '_fileHandler', $writer);
    }
}
