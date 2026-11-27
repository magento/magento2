<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

use Magento\Framework\Registry;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\WebsiteFactory;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Credit memo grids must render rows of a store view that no longer exists.
 */
#[AppArea('adminhtml')]
#[DbIsolation(false)]
class GridWithDeletedWebsiteTest extends AbstractBackendController
{
    #[DataProvider('gridNamespaceDataProvider')]
    #[DataFixture('Magento/Sales/_files/order_with_invoice_shipment_creditmemo_on_second_website.php')]
    public function testGridRendersCreditmemoOfDeletedWebsite(string $namespace): void
    {
        $this->deleteWebsite('test');

        $this->getRequest()->getHeaders()->addHeaderLine('Accept', 'application/json');
        $this->getRequest()->setParams([
            'namespace' => $namespace,
            'filters' => ['order_increment_id' => '200000001'],
        ]);
        $this->dispatch('backend/mui/index/render/');

        $data = json_decode($this->getResponse()->getBody(), true);
        $this->assertArrayNotHasKey('error', $data, $data['error'] ?? '');
        $this->assertSame(1, $data['totalRecords']);
        $this->assertSame('200000001', $data['items'][0]['increment_id']);
    }

    /**
     * @return array
     */
    public static function gridNamespaceDataProvider(): array
    {
        return [
            'credit memos grid' => ['sales_order_creditmemo_grid'],
            'order view credit memos tab' => ['sales_order_view_creditmemo_grid'],
        ];
    }

    /**
     * @param string $code
     * @return void
     */
    private function deleteWebsite(string $code): void
    {
        $registry = $this->_objectManager->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        $website = $this->_objectManager->get(WebsiteFactory::class)->create();
        $websiteResource = $this->_objectManager->get(WebsiteResource::class);
        $websiteResource->load($website, $code, 'code');
        $websiteResource->delete($website);

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
