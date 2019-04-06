<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Setup\Patch\Data;

use Magento\CheckoutAgreements\Model\Agreement;
use Magento\CheckoutAgreements\Model\Config\Source\AgreementForms;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Need to save compatibility.
 */
class SetAllAgreementsForCheckoutOnly implements DataPatchInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $collection = $this->collectionFactory->create();
        $connection = $this->moduleDataSetup->getConnection();

        /** @var Agreement $agreement */
        foreach ($collection as $agreement) {
            $formArray = [
                'agreement_id' => $agreement->getId(),
                'used_in_forms' => AgreementForms::CHECKOUT_CODE
            ];
            $connection->insert($connection->getTableName('checkout_agreement_used_in_forms'), $formArray);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
