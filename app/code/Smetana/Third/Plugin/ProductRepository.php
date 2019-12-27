<?php
namespace Smetana\Third\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Smetana\Third\Api\Data\PartnerInterface;
use Smetana\Third\Model\PartnerRepository;

/**
 * Override product repository operations plugin class
 *
 * @package Smetana\Third\Plugin
 */
class ProductRepository
{
    /**
     * Partner repository instance
     *
     * @var PartnerRepository
     */
    private $partnerRepository;

    /**
     * @param PartnerRepository $partnerRepository
     */
    public function __construct(
        PartnerRepository $partnerRepository
    ) {
        $this->partnerRepository = $partnerRepository;
    }

    /**
     * Change product data
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $entity
     *
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function afterGetById(
        ProductRepositoryInterface $subject,
        ProductInterface $entity
    ): ProductInterface {
        /** @var PartnerInterface $partner */
        $partner = $this->partnerRepository->getById($entity->getId(), PartnerInterface::PRODUCT_ID);
        $extensionAttributes = $entity->getExtensionAttributes();
        $extensionAttributes->setPartner($partner);
        $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
    }
}
