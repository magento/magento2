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
namespace Magento\Backend\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class DashboardTest extends \Magento\Backend\Utility\Controller
{
    public function testAjaxBlockAction()
    {
        $this->getRequest()->setParam('block', 'tab_orders');
        $this->dispatch('backend/admin/dashboard/ajaxBlock');

        $actual = $this->getResponse()->getBody();
        $this->assertContains('dashboard-diagram', $actual);
    }

    public function testTunnelAction()
    {
        $testUrl = \Magento\Backend\Block\Dashboard\Graph::API_URL . '?cht=p3&chd=t:60,40&chs=250x100&chl=Hello|World';
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $testUrl);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        try {
            if (false === curl_exec($handle)) {
                $this->markTestSkipped('Third-party service is unavailable: ' . $testUrl);
            }
            curl_close($handle);
        } catch (\Exception $e) {
            curl_close($handle);
            throw $e;
        }

        $gaData = array(
            'cht' => 'lc',
            'chf' => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
            'chm' => 'B,f4d4b2,0,0,0',
            'chco' => 'db4814',
            'chd' => 'e:AAAAAAAAf.AAAA',
            'chxt' => 'x,y',
            'chxl' => '0:|10/13/12|10/14/12|10/15/12|10/16/12|10/17/12|10/18/12|10/19/12|1:|0|1|2',
            'chs' => '587x300',
            'chg' => '16.666666666667,50,1,0'
        );
        $gaFixture = urlencode(base64_encode(json_encode($gaData)));

        /** @var $helper \Magento\Backend\Helper\Dashboard\Data */
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Helper\Dashboard\Data'
        );
        $hash = $helper->getChartDataHash($gaFixture);
        $this->getRequest()->setParam('ga', $gaFixture)->setParam('h', $hash);
        $this->dispatch('backend/admin/dashboard/tunnel');
        $this->assertStringStartsWith("\x89\x50\x4E\x47", $this->getResponse()->getBody()); // PNG header
    }
}
