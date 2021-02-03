<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class DashboardTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testAjaxBlockAction()
    {
        $this->getRequest()->setParam('block', 'tab_orders');
        $this->dispatch('backend/admin/dashboard/ajaxBlock');

        $actual = $this->getResponse()->getBody();
        $this->assertStringContainsString('dashboard-diagram', $actual);
    }

    /**
     * Tests tunnelAction
     *
     * @throws \Exception
     * @return void
     */
    public function testTunnelAction()
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
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
        // phpcs:enable

        $gaData = [
            'cht' => 'lc',
            'chf' => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
            'chm' => 'B,f4d4b2,0,0,0',
            'chco' => 'db4814',
            'chd' => 'e:AAAAAAAAf.AAAA',
            'chxt' => 'x,y',
            'chxl' => '0:|10/13/12|10/14/12|10/15/12|10/16/12|10/17/12|10/18/12|10/19/12|1:|0|1|2',
            'chs' => '587x300',
            'chg' => '16.666666666667,50,1,0',
        ];
        $gaFixture = urlencode(base64_encode(json_encode($gaData)));

        /** @var $helper \Magento\Backend\Helper\Dashboard\Data */
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Backend\Helper\Dashboard\Data::class
        );
        $hash = $helper->getChartDataHash($gaFixture);
        $this->getRequest()->setParam('ga', $gaFixture)->setParam('h', $hash);
        $this->dispatch('backend/admin/dashboard/tunnel');
        $this->assertStringStartsWith("\x89\x50\x4E\x47", $this->getResponse()->getBody()); // PNG header
    }
}
