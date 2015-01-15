<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Model\System\Config\Backend;

class LinksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Contact\Model\System\Config\Backend\Links|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Links(
            $this->getMock('\Magento\Framework\Model\Context', [], [], '', false),
            $this->getMock('\Magento\Framework\Registry', [], [], '', false),
            $this->getMockForAbstractClass('\Magento\Framework\App\Config\ScopeConfigInterface', [], '', false),
            $this->getMockForAbstractClass('\Magento\Framework\Model\Resource\AbstractResource', [], '', false),
            $this->getMock('\Magento\Framework\Data\Collection\Db', [], [], '', false)
        );
    }

    public function testGetIdentities()
    {
        $this->assertTrue(is_array($this->_model->getIdentities()));
    }
}
