<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Controller\Adminhtml\Json;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class CountryRegionTest extends AbstractBackendController
{
    /**
     * Test Execute without param
     */
    public function testExecuteWithNoCountryParam()
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue([]);
        $this->dispatch('backend/directory/json/countryRegion');

        $actual = $this->getResponse()->getBody();

        $this->assertEquals('[]', $actual);
    }

    /**
     * Test Execute with region in the fixture
     *
     * @magentoDataFixture Magento/Directory/_files/example_region_in_country.php
     */
    public function testExecute()
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue([
            'parent' => 'WW'
        ]);
        $this->dispatch('backend/directory/json/countryRegion');

        $actual = $this->getResponse()->getBody();

        $this->assertStringContainsString('Example Region 1', $actual);
        $this->assertStringContainsString('Example Region 2', $actual);
    }
}
