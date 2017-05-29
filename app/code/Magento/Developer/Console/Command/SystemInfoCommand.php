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
     * Constructor
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\State $appState
    ) {
        parent::__construct('info:system');
        $this->productMetadataInterface = $productMetadataInterface;
        $this->deploymentConfig = $deploymentConfig;
        $this->moduleList = $moduleList;
        $this->customerFactory = $customerFactory;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->attributeFactory = $attributeFactory;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->paymentConfig = $paymentConfig;
        $this->appState = $appState;
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
            ->setHeaders(array('Name', 'Value'))
            ->setRows(array(
                array('Application', $this->productMetadataInterface->getName() . ' ' . $this->productMetadataInterface->getEdition()),
                array('Version', $this->productMetadataInterface->getVersion()),
                new TableSeparator(),

                array('Application Mode', $this->deploymentConfig->get(AppState::PARAM_MODE)),
                array('Session', $this->deploymentConfig->get('session/save')),
                array('Crypt Key', $this->deploymentConfig->get('crypt/key')),
                array('Secure URLs at Storefront', $this->scopeConfigInterface->getValue('web/secure/use_in_frontend') ? 'Yes' : 'No'),
                array('Secure URLs in Admin', $this->scopeConfigInterface->getValue('web/secure/use_in_adminhtml')),
                array('Install Date', $this->deploymentConfig->get('install/date')),
                array('Module Vendors', $this->getModuleVendors()),
                new TableSeparator(),

                array('Total Products', $this->getProductCount()),
                array('Total Categories', $this->getProductCount()),
                array('Total Attributes', $this->getAttributeCount()),
                array('Total Customers', $this->getCustomerCount()),
                array('Active Payment Methods', $this->getActivePaymentMethods()),
            ))
        ;
        $table->render();

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
     * @return number
     */
    protected function getProductCount()
    {
        return $this->productFactory
            ->create()
            ->getCollection()
            ->getSize();
    }

    /**
     * @return number
     */
    protected function getCategoryCount()
    {
        return $this->categoryFactory
            ->create()
            ->getCollection()
            ->getSize();
    }

    /**
     * @return number
     */
    protected function getAttributeCount()
    {
        return $this->attributeFactory
            ->create()
            ->getCollection()
            ->getSize();
    }

    /**
     * @return number
     */
    protected function getCustomerCount()
    {
        return $this->customerFactory->create()
            ->getCollection()
            ->getSize();
    }


    public function getActivePaymentMethods()
    {
        return $this->appState->emulateAreaCode(
            'adminhtml',
            function() {
                $payments = $this->paymentConfig->getActiveMethods();
                $paymentTitles = array();

                foreach ($payments as $paymentCode => $paymentModel) {
                    $paymentTitles[] = $this->scopeConfigInterface
                        ->getValue('payment/'.$paymentCode.'/title');
                }

                return count($paymentTitles) > 1 ? implode(', ', $paymentTitles) : 'none';
            });
    }
}
