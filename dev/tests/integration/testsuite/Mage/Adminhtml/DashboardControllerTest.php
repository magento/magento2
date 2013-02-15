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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Adminhtml_DashboardControllerTest extends Mage_Backend_Utility_Controller
{
    public function testTunnelAction()
    {
        $testUrl = Mage_Adminhtml_Block_Dashboard_Graph::API_URL . '?cht=p3&chd=t:60,40&chs=250x100&chl=Hello|World';
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $testUrl);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        try {
            if (false === curl_exec($handle)) {
                $this->markTestSkipped('Third-party service is unavailable: ' . $testUrl);
            }
            curl_close($handle);
        } catch (Exception $e) {
            curl_close($handle);
            throw $e;
        }

        $gaFixture = 'YTo5OntzOjM6ImNodCI7czoyOiJsYyI7czozOiJjaGYiO3M6Mzk6ImJnLHMsZjRmNGY0fGMsbGcsOTAsZmZmZmZ'
            . 'mLDAuMSxlZGVkZWQsMCI7czozOiJjaG0iO3M6MTQ6IkIsZjRkNGIyLDAsMCwwIjtzOjQ6ImNoY28iO3M6NjoiZGI0ODE0IjtzOjM6ImN'
            . 'oZCI7czoxNjoiZTpBQUFBQUFBQWYuQUFBQSI7czo0OiJjaHh0IjtzOjM6IngseSI7czo0OiJjaHhsIjtzOjc0OiIwOnwxMC8xMy8xMnw'
            . 'xMC8xNC8xMnwxMC8xNS8xMnwxMC8xNi8xMnwxMC8xNy8xMnwxMC8xOC8xMnwxMC8xOS8xMnwxOnwwfDF8MiI7czozOiJjaHMiO3M6Nzo'
            . 'iNTg3eDMwMCI7czozOiJjaGciO3M6MjI6IjE2LjY2NjY2NjY2NjY2Nyw1MCwxLDAiO30%3D';
        /** @var $helper Mage_Adminhtml_Helper_Dashboard_Data */
        $helper = Mage::helper('Mage_Adminhtml_Helper_Dashboard_Data') ;
        $hash = $helper->getChartDataHash($gaFixture);
        $this->getRequest()->setParam('ga', $gaFixture)->setParam('h', $hash);
        $this->dispatch('backend/admin/dashboard/tunnel');
        $this->assertStringStartsWith("\x89\x50\x4E\x47", $this->getResponse()->getBody()); // PNG header
    }
}
