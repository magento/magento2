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
namespace Magento\Store\Model;

use Magento\TestFramework\Helper\Bootstrap;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Bootstrap::getObjectManager()->create('Magento\Store\Model\Group');
    }

    public function testSetGetWebsite()
    {
        $this->assertFalse($this->_model->getWebsite());
        $website = Bootstrap::getObjectManager()->get('Magento\Framework\StoreManagerInterface')->getWebsite();
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
        $website = Bootstrap::getObjectManager()->get('Magento\Framework\StoreManagerInterface')->getWebsite();
        $this->_model->setWebsite($website);
        // Empty string should get treated like no parameter
        $actualResult = $this->_model->getWebsite('');
        $this->assertSame($website, $actualResult);
        // Null string should get treated like no parameter
        $actualResult = $this->_model->getWebsite(null);
        $this->assertSame($website, $actualResult);
    }
}
