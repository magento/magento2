<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export;

use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit\Framework\Attributes\DataProvider;

class GetFilterTest extends AbstractBackendController
{
    /**
     * @var string|null
     */
    private ?string $httpXRequestedWith = null;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->httpXRequestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'];
        }
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->httpXRequestedWith !== null) {
            $_SERVER['HTTP_X_REQUESTED_WITH'] = $this->httpXRequestedWith;
        }

        parent::tearDown();
    }

    #[DataProvider('entityDataProvider')]
    public function testExecute(string $entity, array $expectedFilters): void
    {
        $this->getRequest()->setMethod('POST')
            ->setParams(['isAjax' => 'true']);
        $this->getRequest()->getHeaders()
            ->addHeaderLine('X_REQUESTED_WITH', 'XMLHttpRequest');
        $this->dispatch('backend/admin/export/getFilter/entity/' . $entity);
        $body = $this->getResponse()->getBody();
        foreach ($expectedFilters as $expectedFilter) {
            $this->assertStringContainsString("name=\"$expectedFilter\"", $body);
        }
    }

    public static function entityDataProvider(): array
    {
        return [
            'catalog_product' => [
                'catalog_product',
                [
                    'export_filter[sku]',
                    'export_filter[website_ids][]'
                ]
            ],
            'advanced_pricing' => [
                'advanced_pricing',
                [
                    'export_filter[sku]',
                    'export_filter[website_ids][]'
                ]
            ],
            'customer' => [
                'customer',
                [
                    'export_filter[email]',
                ]
            ]
        ];
    }
}
