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
class IsEuCountryTest extends AbstractBackendController
{
    /**
     * Test Execute without param
     */
    public function testExecuteWithNoCountryParam()
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_GET);
        $this->getRequest()->setParams([]);
        $this->dispatch('backend/directory/json/isEuCountry');

        $actual = $this->getResponse()->getBody();

        $this->assertEquals('false', $actual);
    }

    /**
     * Test Execute with region in the fixture
     *@dataProvider countryDataProvider
     */
    public function testExecute($countryCode, $expected)
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_GET);
        $this->getRequest()->setParams(['countryCode' => $countryCode]);

        $this->dispatch('backend/directory/json/isEuCountry');

        $actual = $this->getResponse()->getBody();

        $this->assertEquals($expected, $actual);
    }

    public function countryDataProvider(): array
    {
        return [
            'Country Code is in EU Country list' => ['DE', 'true'],
            'Country Code not in EU Country list' => ['GB', 'false'],
        ];
    }
}
