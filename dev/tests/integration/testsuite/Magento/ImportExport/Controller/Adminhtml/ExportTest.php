<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml;

use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class ExportTest extends AbstractBackendController
{
    /**
     * Set value of $_SERVER['HTTP_X_REQUESTED_WITH'] parameter here
     *
     * @var string
     */
    protected $_httpXRequestedWith;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->_httpXRequestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'];
        }
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->_httpXRequestedWith !== null) {
            $_SERVER['HTTP_X_REQUESTED_WITH'] = $this->_httpXRequestedWith;
        }

        parent::tearDown();
    }

    /**
     * Test getFilter action
     *
     * @dataProvider getEntityTypesDataProvider
     *
     * @param string $entityType
     * @param string|null $customerEntityType
     * @param array $expectedAttributes
     */
    public function testGetFilterAction(
        string $entityType,
        ?string $customerEntityType = null,
        array $expectedAttributes = []
    ) {
        $this->getRequest()->setParam('isAjax', true);

        // Provide X_REQUESTED_WITH header in response to mark next action as ajax
        $this->getRequest()->getHeaders()
            ->addHeaderLine('X_REQUESTED_WITH', 'XMLHttpRequest');

        $url = 'backend/admin/export/getFilter/entity/' . $entityType;
        if ($customerEntityType) {
            $url .= '/customer_entity/' . $customerEntityType;
        }
        $this->dispatch($url);

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('<div id="export_filter_grid"', $body);
        foreach ($expectedAttributes as $attribute) {
            $this->assertStringContainsString("name=\"export_filter[{$attribute}]\"", $body);
        }
    }

    /**
     * Get possible entity types
     *
     * @return array
     */
    public static function getEntityTypesDataProvider()
    {
        return [
            'products' => [
                'entityType' => 'catalog_product',
                'customerEntityType' => null,
                'expectedAttributes' => ['category_ids']
            ],
            'customers' => [
                'entityType' => 'customer'
            ],
            // customer entities
            'customers_customer_entities' => [
                'entityType' => 'customer',
                'customerEntityType' => 'customer'
            ]
        ];
    }

    /**
     * Test index action
     */
    public function testIndexAction()
    {
        $this->dispatch('backend/admin/export/index');

        $body = $this->getResponse()->getBody();

        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                '//fieldset[@id="base_fieldset"]',
                $body
            )
        );
        $this->assertEquals(
            3,
            Xpath::getElementsCountForXpath(
                '//fieldset[@id="base_fieldset"]/div[contains(@class,"field")]',
                $body
            )
        );
    }
}
