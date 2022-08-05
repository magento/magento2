<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\TestFramework\Helper\Bootstrap;

class GroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = Bootstrap::getObjectManager()->create(\Magento\Store\Model\Group::class);
    }

    public function testSetGetWebsite()
    {
        $this->assertFalse($this->_model->getWebsite());
        $website = Bootstrap::getObjectManager()->get(\Magento\Store\Model\StoreManagerInterface::class)->getWebsite();
        $this->_model->setWebsite($website);
        $actualResult = $this->_model->getWebsite();
        $this->assertSame($website, $actualResult);
    }

    /**
     * Tests that getWebsite returns the default site when defaults are passed in for id
     */
    public function testGetWebsiteDefault()
    {
        $this->assertFalse($this->_model->getWebsite());
        $website = Bootstrap::getObjectManager()->get(\Magento\Store\Model\StoreManagerInterface::class)->getWebsite();
        $this->_model->setWebsite($website);
        // Empty string should get treated like no parameter
        $actualResult = $this->_model->getWebsite('');
        $this->assertSame($website, $actualResult);
        // Null string should get treated like no parameter
        $actualResult = $this->_model->getWebsite(null);
        $this->assertSame($website, $actualResult);
    }
}
