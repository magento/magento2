<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category\Link;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Read handler for catalog product link.
 * @since 2.2.0
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var \Magento\Catalog\Api\Data\CategoryLinkInterfaceFactory
     * @since 2.2.0
     */
    private $categoryLinkFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CategoryLink
     * @since 2.2.0
     */
    private $productCategoryLink;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     * @since 2.2.0
     */
    private $dataObjectHelper;

    /**
     * ReadHandler constructor.
     *
     * @param \Magento\Catalog\Api\Data\CategoryLinkInterfaceFactory $categoryLinkFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\CategoryLink $productCategoryLink
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Catalog\Api\Data\CategoryLinkInterfaceFactory $categoryLinkFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CategoryLink $productCategoryLink
    ) {
        $this->categoryLinkFactory = $categoryLinkFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->productCategoryLink = $productCategoryLink;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function execute($entity, $arguments = [])
    {
        $categoryLinks = [];
        foreach ($this->productCategoryLink->getCategoryLinks($entity) as $categoryData) {
            /** @var \Magento\Catalog\Api\Data\CategoryLinkInterface $categoryLink  */
            $categoryLink = $this->categoryLinkFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $categoryLink,
                $categoryData,
                \Magento\Catalog\Api\Data\CategoryLinkInterface::class
            );
            $categoryLinks[] = $categoryLink;
        }

        $extensionAttributes = $entity->getExtensionAttributes();
        $extensionAttributes->setCategoryLinks(!empty($categoryLinks) ? $categoryLinks : null);
        $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
    }
}
