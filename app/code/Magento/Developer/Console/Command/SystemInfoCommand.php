<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Console\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Magento\Framework\App\State as AppState;

class SystemInfoCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * Console Command Name
     */
    const COMMAND = 'info:system';

    /**
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param AppState $appState
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Payment\Model\Config $paymentConfig
    ) {
        parent::__construct(self::COMMAND);
        $this->productMetadata = $productMetadata;
        $this->deploymentConfig = $deploymentConfig;
        $this->scopeConfig = $scopeConfig;
        $this->appState = $appState;
        $this->moduleList = $moduleList;
        $this->customerFactory = $customerFactory;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->attributeFactory = $attributeFactory;
        $this->paymentConfig = $paymentConfig;
    }

    protected function configure()
    {
        parent::configure();
        $this->setDescription('Get information about Magento setup and installation');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);

        $table
            ->setHeaders(['Item', 'Value'])
            ->setRows([
                ['Application', $this->getApplicationName()],
                ['Version', $this->productMetadata->getVersion()],
                new TableSeparator(),

                ['Application Mode', $this->deploymentConfig->get(AppState::PARAM_MODE)],
                ['Session', $this->deploymentConfig->get('session/save')],
                ['Crypt Key', $this->deploymentConfig->get('crypt/key')],
                ['Secure URLs at Storefront', $this->getSecurityInfo('frontend')],
                ['Secure URLs in Admin', $this->getSecurityInfo('adminhtml')],
                ['Install Date', $this->deploymentConfig->get('install/date')],
                ['Module Vendors', $this->getModuleVendors()],
                new TableSeparator(),

                ['Total Products', $this->getProductCount()],
                ['Total Categories', $this->getProductCount()],
                ['Total Attributes', $this->getAttributeCount()],
                ['Total Customers', $this->getCustomerCount()],
                ['Active Payment Methods', $this->getActivePaymentMethods()],
            ]);

        $table->render();
    }

    /**
     * @return string
     */
    protected function getApplicationName()
    {
        return $this->productMetadata->getName() . ' ' . $this->productMetadata->getEdition();
    }

    /**
     * @return string
     */
    protected function getModuleVendors()
    {
        $vendors = [];
        $moduleList = $this->moduleList->getAll();

        foreach ($moduleList as $moduleName => $info) {
            $moduleNameData = explode('_', $moduleName);

            if (isset($moduleNameData[0])) {
                $vendors[] = $moduleNameData[0];
            }
        }

        return implode(', ', array_unique($vendors));
    }

    /**
     * @return int
     */
    protected function getProductCount()
    {
        return $this->productFactory
            ->create()
            ->getCollection()
            ->getSize();
    }

    /**
     * @return int
     */
    protected function getCategoryCount()
    {
        return $this->categoryFactory
            ->create()
            ->getCollection()
            ->getSize();
    }

    /**
     * @return int
     */
    protected function getAttributeCount()
    {
        return $this->attributeFactory
            ->create()
            ->getCollection()
            ->getSize();
    }

    /**
     * @return int
     */
    protected function getCustomerCount()
    {
        return $this->customerFactory->create()
            ->getCollection()
            ->getSize();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getActivePaymentMethods()
    {
        return $this->appState->emulateAreaCode(
            'adminhtml',
            function() {
                $payments = $this->paymentConfig->getActiveMethods();
                $paymentTitles = [];

                foreach ($payments as $paymentCode => $paymentModel) {
                    $paymentTitles[] = $this->scopeConfig
                        ->getValue('payment/'.$paymentCode.'/title');
                }

                return count($paymentTitles) > 1 ? implode(', ', $paymentTitles) : 'none';
            });
    }

    /**
     * @param $area
     * @return string
     */
    protected function getSecurityInfo($area)
    {
        return $this->scopeConfig->getValue('web/secure/use_in_' . $area) ? 'Yes' : 'No';
    }
}
