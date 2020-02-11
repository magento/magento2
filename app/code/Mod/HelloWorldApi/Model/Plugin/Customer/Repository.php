<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldApi\Model\Plugin\Customer;

use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Mod\HelloWorldApi\Api\Data\ExtraAbilitiesInterface;
use Magento\Framework\Api\SearchResults;
use Mod\HelloWorldApi\Model\ExtraAbilityFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Extra abilities Repository plugin class.
 */
class Repository
{
    /** @var CustomerExtensionFactory */
    private $customerExtensionFactory;

    /** @var CustomerInterface */
    private $currentCustomer;

    /** @var  EntityManager */
    private $entityManager;

    /** @var MetadataPool */
    private $metadataPool;

    /** @var  ResourceConnection\ */
    private $resourceConnection;

    /** @var  ExtraAbilityFactory */
    private $extraAbilityFactory;

    /**
     * Repository constructor.
     * @param CustomerExtensionFactory $customerExtensionFactory
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param ExtraAbilityFactory $extraAbilityFactory
     */
    public function __construct(
        CustomerExtensionFactory $customerExtensionFactory,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        ExtraAbilityFactory $extraAbilityFactory
    ) {
        $this->customerExtensionFactory = $customerExtensionFactory;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->extraAbilityFactory = $extraAbilityFactory;
    }

    /**
     * Add Extra Abilities to customer extension attributes
     *
     * @param CustomerRepositoryInterface $subject
     * @param SearchResults $searchResult
     * @return SearchResults
     * @throws \Exception
     */
    public function afterGetList(
        CustomerRepositoryInterface $subject,
        SearchResults $searchResult
    ) {
        /** @var CustomerInterface $customer */
        foreach ($searchResult->getItems() as $customer) {
            $this->addExtraAbilitiesToCustomer($customer);
        }

        return $searchResult;
    }

    /**
     * Before save plugin.
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return void
     */
    public function beforeSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $customer
    ) {
        $this->currentCustomer = $customer;
    }

    /**
     * After get by id plugin.
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @throws \Exception
     */
    public function afterGetById(
        CustomerRepositoryInterface $subject,
        CustomerInterface $customer
    ) {
        $this->addExtraAbilitiesToCustomer($customer);
        return $customer;
    }

    /**
     * After save plugin.
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @throws \Exception
     */
    public function afterSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $customer
    ) {
        if ($this->currentCustomer !== null) {
            /** @var CustomerInterface $previosCustomer */
            $extensionAttributes = $this->currentCustomer->getExtensionAttributes();

            if ($extensionAttributes && $extensionAttributes->getExtraAbilities()) {
                /** @var ExtraAbilitiesInterface $extraAbilities */
                $extraAbilities = $extensionAttributes->getExtraAbilities();
                if (is_array($extraAbilities)) {
                    /** @var ExtraAbilitiesInterface $extraAbility */
                    foreach ($extraAbilities as $extraAbility) {
                        $extraAbility->setCustomerId((int)$customer->getId());
                        $this->entityManager->save($extraAbility);
                    }
                }
            }
            $this->currentCustomer = null;
        }

        return $customer;
    }

    /**
     * Add extra abilities to the current customer.
     *
     * @param CustomerInterface $customer
     * @return self
     * @throws \Exception
     */
    public function addExtraAbilitiesToCustomer(CustomerInterface $customer)
    {
        $extensionAttributes = $customer->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            $extensionAttributes = $this->customerExtensionFactory->create();
        }

        $extraAbilities = [];
        $metadata = $this->metadataPool->getMetadata(ExtraAbilitiesInterface::class);
        $connection = $this->resourceConnection->getConnection();

        $select = $connection
            ->select()
            ->from($metadata->getEntityTable(), ExtraAbilitiesInterface::ABILITY_ID)
            ->where(ExtraAbilitiesInterface::CUSTOMER_ID . ' = ?', (int)$customer->getId());

        $ids = $connection->fetchCol($select);

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $extraAbility = $this->extraAbilityFactory->create();
                $extraAbilities[] = $this->entityManager->load($extraAbility, $id);
            }
        }
        $extensionAttributes->setExtraAbilities($extraAbilities);
        $customer->setExtensionAttributes($extensionAttributes);

        return $this;
    }
}
