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
 * @category    Magento
 * @package     Magento_Captcha
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Captcha_Block_Captcha_ZendTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Captcha_Block_Captcha_Zend
     */
    protected $_block;

    public function setUp()
    {
         $this->_block = Mage::app()->getLayout()
            ->createBlock('Mage_Captcha_Block_Captcha_Zend');
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetRefreshUrlWhenFrontendStore()
    {
        $this->assertContains('captcha/refresh', $this->_block->getRefreshUrl());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetRefreshUrlWhenIsAdminStore()
    {
        Mage::app()->getStore('admin')->setUrlClassName('Mage_Backend_Model_Url');
        Mage::app()->setCurrentStore(Mage::app()->getStore('admin'));

        $this->assertContains('backend/admin/refresh/refresh', $this->_block->getRefreshUrl());
    }
}
