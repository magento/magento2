<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Controller\Adminhtml\Dashboard;

use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * @magentoAppArea adminhtml
 */
class AjaxBlockTest extends AbstractBackendController
{
    /**
     * Test execute to check render block
     *
     * @param string $block
     * @param string $expectedResult
     *
     * @dataProvider ajaxBlockDataProvider
     */
    public function testExecute($block, $expectedResult)
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParam('block', $block);

        $this->dispatch('backend/admin/dashboard/ajaxBlock/');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $actual = $this->getResponse()->getBody();

        $this->assertStringContainsString($expectedResult, $actual);
    }

    /**
     * Provides POST data and Expected Result
     *
     * @return array
     */
    public static function ajaxBlockDataProvider(): array
    {
        return [
            [
                'totals',
                'dashboard_diagram_totals'
            ],
            [
                '',
                ''
            ],
            [
                'test_block',
                ''
            ]
        ];
    }
}
