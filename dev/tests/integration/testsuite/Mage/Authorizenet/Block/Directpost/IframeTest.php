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
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Authorizenet
 */
class Mage_Authorizenet_Block_Directpost_IframeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testToHtml()
    {
        $xssString = '</script><script>alert("XSS")</script>';
        $block = new Mage_Authorizenet_Block_Directpost_Iframe();
        $block->setTemplate('directpost/iframe.phtml');
        $block->setParams(array(
            'redirect' => $xssString,
            'redirect_parent' => $xssString,
            'error_msg' => $xssString,
        ));
        $content = $block->toHtml();
        $this->assertNotContains($xssString, $content, 'Params mast be escaped');
        $this->assertContains(htmlspecialchars($xssString), $content, 'Content must present');
    }
}