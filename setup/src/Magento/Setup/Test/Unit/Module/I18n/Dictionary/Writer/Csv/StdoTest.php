<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary\Writer\Csv;

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
        /** @var \Magento\Setup\Module\I18n\Dictionary\Writer\Csv $writer */
        $writer = $objectManagerHelper->getObject(\Magento\Setup\Module\I18n\Dictionary\Writer\Csv\Stdo::class);

        $this->assertAttributeEquals($this->_handler, '_fileHandler', $writer);
    }
}
