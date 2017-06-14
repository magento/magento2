<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml;

use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Json\Helper\Data;
use Magento\Tax\Api\Data\TaxRateInterfaceFactory;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Tax\Api\Data\TaxRateInterface;
use Magento\Tax\Model\TaxRuleFixtureFactory;
use Magento\Tax\Model\Rate\Provider as RatesProvider;

/**
 * @magentoAppArea adminhtml
 */
class RuleTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * TaxRate factory
     *
     * @var TaxRateInterfaceFactory
     */
    private $taxRateFactory;

    /**
     * TaxRateService
     *
     * @var TaxRateRepositoryInterface
     */
    private $rateRepository;

    /**
     * Helps in creating required tax rules.
     *
     * @var TaxRuleFixtureFactory
     */
    private $taxRateFixtureFactory;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var  RegionFactory
     */
    private $regionFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->rateRepository = $this->_objectManager->get(TaxRateRepositoryInterface::class);
        $this->taxRateFactory = $this->_objectManager->create(TaxRateInterfaceFactory::class);
        $this->dataObjectHelper = $this->_objectManager->create(DataObjectHelper::class);
        $this->taxRateFixtureFactory = new TaxRuleFixtureFactory();
        $this->countryFactory = $this->_objectManager->create(CountryFactory::class);
        $this->regionFactory = $this->_objectManager->create(RegionFactory::class);

        $this->_generateTaxRates();
    }

    /**
     * Tests request of tax rates collection set.
     *
     * @param array $postData
     * @param int $itemsCount
     * @dataProvider ajaxActionDataProvider
     * @magentoDbIsolation enabled
     */
    public function testAjaxLoadRates($postData, $itemsCount)
    {
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/tax/rule/ajaxLoadRates');
        $jsonBody = $this->getResponse()->getBody();

        $response = Bootstrap::getObjectManager()->get(Data::class)
            ->jsonDecode($jsonBody);

        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('errorMessage', $response);
        $this->assertEmpty($response['errorMessage']);
        $this->assertArrayHasKey('result', $response);
        $this->assertCount($itemsCount, $response['result']);
    }

    /**
     * Creates tax rates items in repository.
     */
    private function _generateTaxRates()
    {
        $ratesCount = RatesProvider::PAGE_SIZE + 1;
        for ($i = 0; $i <= $ratesCount; $i++) {
            $taxData = [
                'tax_country_id' => 'US',
                'tax_region_id' => '8',
                'rate' => '8.25',
                'code' => 'US-CA-*-Rate' . $i . rand(),
                'zip_is_range' => true,
                'zip_from' => 78765,
                'zip_to' => 78780,
            ];

            // Tax rate data object created
            $taxRate = $this->taxRateFactory->create();
            $this->dataObjectHelper->populateWithArray($taxRate, $taxData, TaxRateInterface::class);

            //Tax rate service call
            $this->rateRepository->save($taxRate);
        }
    }

    /**
     * Provides POST data
     *
     * @return array
     */
    public function ajaxActionDataProvider()
    {
        return [
            [['p' => 1], RatesProvider::PAGE_SIZE],
            [['p' => 1, 's' => 'no_such_code'], 0]
        ];
    }
}
