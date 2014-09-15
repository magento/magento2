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
namespace Magento\Bundle\Service\V1\Product\Option;

use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Service\V1\Data\Product\Link;
use Magento\Bundle\Service\V1\Data\Product\Option;
use Magento\Bundle\Service\V1\Product\Link\WriteService as LinkWriteService;
use Magento\Bundle\Service\V1\Data\Product\OptionConverter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\StoreManagerInterface;
use Magento\Webapi\Exception;

class WriteService implements WriteServiceInterface
{
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var Type
     */
    private $type;
    /**
     * @var \Magento\Bundle\Service\V1\Data\Product\OptionConverter
     */
    private $optionConverter;
    /**
     * @var \Magento\Store\Model\StoreManager
     */
    private $storeManager;

    /**
     * @var LinkWriteService
     */
    private $linkWriteService;

    /**
     * @param ProductRepository $productRepository
     * @param Type $type
     * @param OptionConverter $optionConverter
     * @param StoreManagerInterface $storeManager
     * @param LinkWriteService $linkWriteService
     */
    public function __construct(
        ProductRepository $productRepository,
        Type $type,
        OptionConverter $optionConverter,
        StoreManagerInterface $storeManager,
        LinkWriteService $linkWriteService
    ) {
        $this->productRepository = $productRepository;
        $this->type = $type;
        $this->optionConverter = $optionConverter;
        $this->storeManager = $storeManager;
        $this->linkWriteService = $linkWriteService;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($productSku, $optionId)
    {
        $product = $this->getProduct($productSku);
        $optionCollection = $this->type->getOptionsCollection($product);
        $optionCollection->setIdFilter($optionId);

        /** @var \Magento\Bundle\Model\Option $removeOption */
        $removeOption = $optionCollection->getFirstItem();
        if (!$removeOption->getId()) {
            throw new NoSuchEntityException('Requested option doesn\'t exist');
        }
        $removeOption->delete();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function add($productSku, Option $option)
    {
        $product = $this->getProduct($productSku);
        $optionModel = $this->optionConverter->createModelFromData($option, $product);
        $optionModel->setStoreId($this->storeManager->getStore()->getId());

        try {
            $optionModel->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not save option', [], $e);
        }

        $optionId = $optionModel->getId();
        if (is_array($option->getProductLinks())) {
            foreach ($option->getProductLinks() as $link) {
                $this->linkWriteService->addChild($productSku, $optionId, $link);
            }
        }

        return $optionId;
    }

    /**
     * {@inheritdoc}
     */
    public function update($productSku, $optionId, \Magento\Bundle\Service\V1\Data\Product\Option $option)
    {
        $product = $this->getProduct($productSku);
        $optionCollection = $this->type->getOptionsCollection($product);
        $optionCollection->setIdFilter($optionId);

        /** @var \Magento\Bundle\Model\Option $optionModel */
        $optionModel = $optionCollection->getFirstItem();
        $updateOption = $this->optionConverter->getModelFromData($option, $optionModel);

        if (!$updateOption->getId()) {
            throw new NoSuchEntityException('Requested option doesn\'t exist');
        }
        $updateOption->setStoreId($this->storeManager->getStore()->getId());

        /**
         * @var Link[] $existingProductLinks
         */
        $existingProductLinks = $optionModel->getProductLinks();
        if (!is_array($existingProductLinks)) {
            $existingProductLinks = array();
        }
        /**
         * @var Link[] $newProductLinks
         */
        $newProductLinks = $option->getProductLinks();
        if (is_null($newProductLinks)) {
            $newProductLinks = array();
        }
        /**
         * @var Link[] $linksToDelete
         */
        $linksToDelete = array_udiff($existingProductLinks, $newProductLinks, array($this, 'compareLinks'));
        foreach ($linksToDelete as $link) {
            $this->linkWriteService->removeChild($productSku, $option->getId(), $link->getSku());
        }
        /**
         * @var Link[] $linksToAdd
         */
        $linksToAdd = array_udiff($newProductLinks, $existingProductLinks, array($this, 'compareLinks'));
        foreach ($linksToAdd as $link) {
            $this->linkWriteService->addChild($productSku, $option->getId(), $link);
        }

        try {
            $updateOption->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not save option', [], $e);
        }

        return true;
    }

    /**
     * @param string $productSku
     * @return Product
     * @throws Exception
     */
    private function getProduct($productSku)
    {
        $product = $this->productRepository->get($productSku);

        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new Exception(
                'Product with specified sku: "%1" is not a bundle product',
                Exception::HTTP_FORBIDDEN,
                Exception::HTTP_FORBIDDEN,
                [
                    $product->getSku()
                ]
            );
        }

        return $product;
    }

    /**
     * Compare two links and determine if they are equal
     *
     * @param Link $firstLink
     * @param Link $secondLink
     * @return int
     */
    private function compareLinks(Link $firstLink, Link $secondLink)
    {
        if ($firstLink->getSku() == $secondLink->getSku()) {
            return 0;
        } else {
            return 1;
        }
    }
}
