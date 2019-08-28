<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Test\Integration\Controller\Adminhtml\Source;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\InventoryAdminUi\Controller\Adminhtml\Source\Save;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Verify Source Save controller processes and saves request data as Source entity correctly.
 *
 * @magentoAppArea adminhtml
 */
class SaveTest extends AbstractBackendController
{
    /**
     * Test subject.
     *
     * @var Save
     */
    private $controller;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->controller = $this->_objectManager->get(Save::class);
    }

    /**
     * Verify, source will be saved with region id and region name if both supplied in request.
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/910317/scenarios/3334660
     * @return void
     */
    public function testExecute(): void
    {
        $requestData = $this->getRequestData();
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->dispatch('backend/inventory/source/save');
        $source = $this->_objectManager->get(SourceRepositoryInterface::class)
            ->get('test_source_with_region_id_and_region');
        $this->assertEquals('test_source_with_region_id_and_region', $source->getSourceCode());
        $this->assertEquals('Ain', $source->getRegion());
        $this->assertEquals('182', $source->getRegionId());
    }

    /**
     * Data for test.
     *
     * @return array
     */
    private function getRequestData(): array
    {
        return [
            'general' =>
                [
                    'source_code' => 'test_source_with_region_id_and_region',
                    'name' => 'Test Source With Region ID And Region',
                    'latitude' => '',
                    'longitude' => '',
                    'contact_name' => '',
                    'email' => '',
                    'phone' => '',
                    'fax' => '',
                    'region' => 'Ain',
                    'city' => '',
                    'street' => '',
                    'postcode' => '12345',
                    'enabled' => '1',
                    'description' => '',
                    'country_id' => 'FR',
                    'region_id' => '182',
                ],
        ];
    }
}
