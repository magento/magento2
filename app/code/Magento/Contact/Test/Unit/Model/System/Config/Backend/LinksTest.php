<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Test\Unit\Model\System\Config\Backend;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class LinksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Contact\Model\System\Config\Backend\Links|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = (new ObjectManager($this))->getObject(
            \Magento\Contact\Model\System\Config\Backend\Links::class
        );
    }

    public function testGetIdentities()
    {
        $this->assertTrue(is_array($this->_model->getIdentities()));
    }
}
