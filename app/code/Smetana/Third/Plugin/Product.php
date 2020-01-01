<?php
namespace Smetana\Third\Plugin;

use Magento\Catalog\Api\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Message\ManagerInterface;
use Smetana\Third\Api\Data\PartnerInterface;
use Smetana\Third\Model\PartnerRepository;

/**
 * Override product plugin class
 *
 * @package Smetana\Third\Plugin
 */
class Product
{
    /**
     * Message manager instance
     *
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * Product extension Factory instance
     *
     * @var Data\ProductExtensionFactory
     */
    private $extensionFactory;

    /**
     * Partner repository instance
     *
     * @var PartnerRepository
     */
    private $partnerRepository;

    /**
     * @param ManagerInterface $messageManager
     * @param Data\ProductExtensionFactory $extensionFactory
     * @param PartnerRepository $partnerRepository
     */
    public function __construct(
        ManagerInterface $messageManager,
        Data\ProductExtensionFactory $extensionFactory,
        PartnerRepository $partnerRepository
    ) {
        $this->messageManager = $messageManager;
        $this->extensionFactory = $extensionFactory;
        $this->partnerRepository = $partnerRepository;
    }

    /**
     * Change extension attributes return value
     *
     * @param Data\ProductInterface $entity
     * @param Data\ProductExtensionInterface|null $extension
     *
     * @return Data\ProductExtension
     */
    public function afterGetExtensionAttributes(
        Data\ProductInterface $entity,
        Data\ProductExtensionInterface $extension = null
    ): Data\ProductExtension {
        if ($extension === null) {
            /** @var Data\ProductExtension $extension */
            $extension = $this->extensionFactory->create();
        }

        return $extension;
    }

    /**
     * Save product id to partner after saving product
     *
     * @param Data\ProductInterface $entity
     * @return Data\ProductInterface
     */
    public function afterSave(Data\ProductInterface $entity): Data\ProductInterface
    {
        if (!is_null($entity->getData(PartnerInterface::PARTNER_ID))) {
            /** @var PartnerInterface $partner */
            $partner = $this->partnerRepository->getById(
                $entity->getData(PartnerInterface::PARTNER_ID),
                PartnerInterface::PARTNER_ID
            );
            $partner->setProductId($entity->getId());

            /** @var SearchCriteriaBuilder $searchCriteria */
            $searchCriteria = ObjectManager::getInstance()->get(SearchCriteriaBuilder::class)
                ->addFilter(PartnerInterface::PARTNER_ID, $partner->getPartnerId(), 'neq')
                ->addFilter(PartnerInterface::PRODUCT_ID, $entity->getId(), 'eq')
                ->create();
            $removePartners = $this->partnerRepository->getList($searchCriteria)->getItems();
            foreach ($removePartners as $removePartner) {
                $removePartner->setProductId('');
                $this->repositorySave($removePartner);
            }

            $this->repositorySave($partner);
        }

        return $entity;
    }

    /**
     * Save partner using repository
     *
     * @param PartnerInterface $partner
     * @return void
     */
    private function repositorySave(PartnerInterface $partner): void
    {
        try {
            $this->partnerRepository->save($partner);
        } catch (StateException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
