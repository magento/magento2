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
 * @package     Phoenix_Moneybookers
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Phoenix_Moneybookers_Block_FormTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Phoenix_Moneybookers_Block_Form
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = new Phoenix_Moneybookers_Block_Form;
    }

    public function testConstruct()
    {
        $this->assertStringEndsWith('form.phtml', $this->_block->getTemplate());
    }

    public function testGetPaymentImageSrc()
    {
        $this->assertStringEndsWith('moneybookers_acc.png', $this->_block->getPaymentImageSrc('moneybookers_acc'));
        $this->assertStringEndsWith('moneybookers_csi.gif', $this->_block->getPaymentImageSrc('moneybookers_csi'));
        $this->assertFalse($this->_block->getPaymentImageSrc('moneybookers_nonexisting'));
    }
}
