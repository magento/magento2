<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Config\Storage\Writer as ConfigWriter;
use Magento\Tax\Api\Data\TaxRateInterface;
use Magento\Tax\Api\Data\TaxRateInterfaceFactory;
use Magento\Tax\Api\Data\TaxRuleInterface;
use Magento\Tax\Api\Data\TaxRuleInterfaceFactory;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\ResourceModel\Calculation\Rate\CollectionFactory;

/**
 * Tax rules fixture generator
 * Tax Config Settings setter for different Tax Modes (for example VAT)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxRulesFixture extends Fixture
{
    const DEFAULT_CUSTOMER_TAX_CLASS_ID = 3;

    const DEFAULT_PRODUCT_TAX_CLASS_ID = 2;

    const DEFAULT_TAX_MODE = 'VAT';

    const DEFAULT_TAX_RATE = 5;

    const DEFAULT_TAX_COUNTRY = 'US';

    /**
     * @var array config paths and values for tax modes
     */
    private $configs = [
        'VAT' => [
            Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX => 1,
            Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX => 1,
            Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT => 1,
            Config::XML_PATH_DISPLAY_SALES_PRICE => Config::DISPLAY_TYPE_INCLUDING_TAX,
            Config::XML_PATH_DISPLAY_SALES_SUBTOTAL => Config::DISPLAY_TYPE_INCLUDING_TAX,
            Config::XML_PATH_DISPLAY_SALES_SHIPPING => Config::DISPLAY_TYPE_INCLUDING_TAX,
            Config::XML_PATH_DISPLAY_SALES_DISCOUNT => Config::DISPLAY_TYPE_INCLUDING_TAX,
            Config::XML_PATH_DISPLAY_SALES_GRANDTOTAL => Config::DISPLAY_TYPE_INCLUDING_TAX,
            Config::XML_PATH_DISPLAY_SALES_FULL_SUMMARY => Config::DISPLAY_TYPE_INCLUDING_TAX,
            Config::CONFIG_XML_PATH_DISPLAY_SHIPPING => Config::DISPLAY_TYPE_INCLUDING_TAX,
            Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE => Config::DISPLAY_TYPE_INCLUDING_TAX,
            Config::XML_PATH_DISPLAY_CART_PRICE => Config::DISPLAY_TYPE_INCLUDING_TAX,
            Config::XML_PATH_DISPLAY_CART_SUBTOTAL => Config::DISPLAY_TYPE_INCLUDING_TAX,
            Config::XML_PATH_DISPLAY_CART_SHIPPING => Config::DISPLAY_TYPE_INCLUDING_TAX,
            Custom::XML_PATH_TAX_WEEE_ENABLE => 1,
        ]
    ];

    /**
     * @var int
     */
    protected $priority = 101;

    /**
     * @var TaxRuleRepositoryInterface
     */
    private $taxRuleRepository;

    /**
     * @var TaxRuleInterfaceFactory
     */
    private $taxRuleFactory;

    /**
     * @var TaxRateInterfaceFactory
     */
    private $taxRateFactory;

    /**
     * @var CollectionFactory
     */
    private $taxRateCollectionFactory;

    /**
     * @var TaxRateRepositoryInterface
     */
    private $taxRateRepository;

    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @param FixtureModel $fixtureModel
     * @param TaxRuleRepositoryInterface $taxRuleRepository
     * @param TaxRuleInterfaceFactory $taxRuleFactory
     * @param CollectionFactory $taxRateCollectionFactory
     * @param TaxRateInterfaceFactory $taxRateFactory
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param ConfigWriter $configWriter
     */
    public function __construct(
        FixtureModel $fixtureModel,
        TaxRuleRepositoryInterface $taxRuleRepository,
        TaxRuleInterfaceFactory $taxRuleFactory,
        CollectionFactory $taxRateCollectionFactory,
        TaxRateInterfaceFactory $taxRateFactory,
        TaxRateRepositoryInterface $taxRateRepository,
        ConfigWriter $configWriter
    ) {
        parent::__construct($fixtureModel);

        $this->taxRuleRepository = $taxRuleRepository;
        $this->taxRuleFactory = $taxRuleFactory;
        $this->taxRateCollectionFactory = $taxRateCollectionFactory;
        $this->taxRateFactory = $taxRateFactory;
        $this->taxRateRepository = $taxRateRepository;
        $this->configWriter = $configWriter;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        //Getting config values
        $taxMode = $this->fixtureModel->getValue('tax_mode', null);
        $taxRules = $this->fixtureModel->getValue('tax_rules', 0);

        if ($taxMode && in_array($taxMode, array_keys($this->configs))) {
            $this->setTaxMode($taxMode);
        }

        $taxRateIds = $this->taxRateCollectionFactory->create()->getAllIds();
        $taxRatesCount = count($taxRateIds);

        while ($taxRules) {
            /** @var $taxRuleDataObject TaxRuleInterface */
            $taxRuleDataObject = $this->taxRuleFactory->create();
            $taxRuleDataObject->setCode('Tax_Rule_' . $taxRules)
                ->setTaxRateIds([$taxRateIds[$taxRules % $taxRatesCount]])
                ->setCustomerTaxClassIds([self::DEFAULT_CUSTOMER_TAX_CLASS_ID])
                ->setProductTaxClassIds([self::DEFAULT_PRODUCT_TAX_CLASS_ID])
                ->setPriority(0)
                ->setPosition(0);

            $this->taxRuleRepository->save($taxRuleDataObject);

            $taxRules--;
        }
    }

    /**
     * Adding appropriate Tax Rate, Tax Rule and Config Settings for selected Tax Mode (for example EU/VAT)
     *
     * @param string $taxMode
     * @return void
     */
    private function setTaxMode($taxMode)
    {
        //Add Tax Rate for selected Tax Mode
        /** @var $taxRate TaxRateInterface */
        $taxRate = $this->taxRateFactory->create();
        $taxRate->setCode($taxMode)
            ->setRate(self::DEFAULT_TAX_RATE)
            ->setTaxCountryId(self::DEFAULT_TAX_COUNTRY)
            ->setTaxPostcode('*');

        $taxRateData = $this->taxRateRepository->save($taxRate);

        //Add Tax Rule for Tax Mode
        /** @var $taxRuleDataObject TaxRuleInterface */
        $taxRuleDataObject = $this->taxRuleFactory->create();
        $taxRuleDataObject->setCode($taxMode)
            ->setTaxRateIds([$taxRateData->getId()])
            ->setCustomerTaxClassIds([self::DEFAULT_CUSTOMER_TAX_CLASS_ID])
            ->setProductTaxClassIds([self::DEFAULT_PRODUCT_TAX_CLASS_ID])
            ->setPriority(0)
            ->setPosition(0);

        $this->taxRuleRepository->save($taxRuleDataObject);

        //Set Tax Mode configs
        $this->setConfigByTaxMode($taxMode);
    }

    /**
     * Set appropriate Tax Config Settings for selected Tax Mode
     *
     * @param string $mode
     * @return void
     */
    private function setConfigByTaxMode($mode = self::DEFAULT_TAX_MODE)
    {
        if (isset($this->configs[$mode]) && is_array($this->configs[$mode])) {
            foreach ($this->configs[$mode] as $configPath => $value) {
                $this->configWriter->save(
                    $configPath,
                    $value
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating tax rules';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [
            'tax_rules' => 'Tax Rules Count',
            'tax_mode' => 'Tax Mode',
        ];
    }
}
