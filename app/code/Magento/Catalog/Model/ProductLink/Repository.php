<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Api\Data;
use \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks as LinksInitializer;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Exception\CouldNotSaveException;

class Repository implements \Magento\Catalog\Api\ProductLinkRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var CollectionProvider
     */
    protected $entityCollectionProvider;

    /**
     * @var LinksInitializer
     */
    protected $linkInitializer;

    /**
     * @var Management
     */
    protected $linkManagement;

    /**
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param CollectionProvider $entityCollectionProvider
     * @param LinksInitializer $linkInitializer
     * @param Management $linkManagement
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\ProductLink\CollectionProvider $entityCollectionProvider,
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $linkInitializer,
        \Magento\Catalog\Model\ProductLink\Management $linkManagement
    ) {
        $this->productRepository = $productRepository;
        $this->entityCollectionProvider = $entityCollectionProvider;
        $this->linkInitializer = $linkInitializer;
        $this->linkManagement = $linkManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Catalog\Api\Data\ProductLinkInterface $entity)
    {
        $linkedProduct = $this->productRepository->get($entity->getLinkedProductSku());
        $product = $this->productRepository->get($entity->getProductSku());
        $links = $this->entityCollectionProvider->getCollection($product, $entity->getLinkType());

        $data = $entity->__toArray();
        foreach ($entity->getCustomAttributes() as $attribute) {
            $data[$attribute->getAttributeCode()] = $attribute->getValue();
        }
        $data['product_id'] = $linkedProduct->getId();
        $links[$linkedProduct->getId()] = $data;
        $this->linkInitializer->initializeLinks($product, [$entity->getLinkType() => $links]);
        try {
            $product->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException('Invalid data provided for linked products');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Catalog\Api\Data\ProductLinkInterface $entity)
    {
        $linkedProduct = $this->productRepository->get($entity->getLinkedProductSku());
        $product = $this->productRepository->get($entity->getProductSku());
        $links = $this->entityCollectionProvider->getCollection($product, $entity->getLinkType());

        if (!isset($links[$linkedProduct->getId()])) {
            throw new NoSuchEntityException(
                sprintf(
                    'Product with SKU %s is not linked to product with SKU %s',
                    $entity->getLinkedProductSku(),
                    $entity->getProductSku()
                )
            );
        }
        //Remove product from the linked product list
        unset($links[$linkedProduct->getId()]);

        $this->linkInitializer->initializeLinks($product, [$entity->getLinkType() => $links]);
        try {
            $product->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException('Invalid data provided for linked products');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($productSku, $type, $linkedProductSku)
    {
        $linkItems = $this->linkManagement->getLinkedItemsByType($productSku, $type);
        /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $linkItem */
        foreach ($linkItems as $linkItem) {
            if ($linkItem->getLinkedProductSku() == $linkedProductSku) {
                return $this->delete($linkItem);
            }
        }
        throw new NoSuchEntityException(
            'Product %s doesn\'t have linked %s as %s',
            [
                $productSku,
                $linkedProductSku,
                $type
            ]
        );
    }
}
