<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class ExportTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Set value of $_SERVER['HTTP_X_REQUESTED_WITH'] parameter here
     *
     * @var string
     */
    protected $_httpXRequestedWith;

    /**
     * Get possible entity types
     *
     * @return array
     */
    public function getEntityTypesDataProvider()
    {
        return [
            'products' => ['$entityType' => 'catalog_product'],
            'customers' => ['$entityType' => 'customer'],
            // customer entities
            'customers_customer_entities' => ['$entityType' => 'customer', '$customerEntityType' => 'customer']
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->_httpXRequestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'];
        }
    }

    protected function tearDown()
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
     * @param string $customerEntityType
     */
    public function testGetFilterAction($entityType, $customerEntityType = null)
    {
        $this->getRequest()->setParam('isAjax', true);

        // Provide X_REQUESTED_WITH header in response to mark next action as ajax
        $this->getRequest()->getHeaders()
            ->addHeaderLine('X_REQUESTED_WITH', 'XMLHttpRequest');

        $url = 'backend/admin/export/getFilter/entity/' . $entityType;
        if ($customerEntityType) {
            $url .= '/customer_entity/' . $customerEntityType;
        }
        $this->dispatch($url);

        $this->assertContains('<div id="export_filter_grid"', $this->getResponse()->getBody());
    }

    /**
     * Test index action
     */
    public function testIndexAction()
    {
        $this->dispatch('backend/admin/export/index');

        $body = $this->getResponse()->getBody();
        $this->assertSelectCount('fieldset#base_fieldset', 1, $body);
        $this->assertSelectCount('fieldset#base_fieldset div.field', 2, $body);
    }
}
