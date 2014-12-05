<?php
/**
 *
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
namespace Magento\Bundle\Model;

use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Webapi\Exception;
use \Magento\Framework\Exception\CouldNotSaveException;

class OptionRepository implements \Magento\Bundle\Api\ProductOptionRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Product\Type
     */
    protected $type;

    /**
     * @var \Magento\Bundle\Api\Data\OptionDataBuilder
     */
    protected $optionBuilder;

    /**
     * @var Resource\Option
     */
    protected $optionResource;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Bundle\Api\ProductLinkManagementInterface
     */
    protected $linkManagement;

    /**
     * @var Product\OptionList
     */
    protected $productOptionList;

    /**
     * @var Product\LinksList
     */
    protected $linkList;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param Product\Type $type
     * @param \Magento\Bundle\Api\Data\OptionDataBuilder $optionBuilder
     * @param Resource\Option $optionResource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Bundle\Api\ProductLinkManagementInterface $linkManagement
     * @param Product\OptionList $productOptionList
     * @param Product\LinksList $linkList
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Bundle\Model\Product\Type $type,
        \Magento\Bundle\Api\Data\OptionDataBuilder $optionBuilder,
        \Magento\Bundle\Model\Resource\Option $optionResource,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Bundle\Api\ProductLinkManagementInterface $linkManagement,
        \Magento\Bundle\Model\Product\OptionList $productOptionList,
        \Magento\Bundle\Model\Product\LinksList $linkList
    ) {
        $this->productRepository = $productRepository;
        $this->type = $type;
        $this->optionBuilder = $optionBuilder;
        $this->optionResource = $optionResource;
        $this->storeManager = $storeManager;
        $this->linkManagement = $linkManagement;
        $this->productOptionList = $productOptionList;
        $this->linkList = $linkList;
    }

    /**
     * {@inheritdoc}
     */
    public function get($productSku, $optionId)
    {
        $product = $this->getProduct($productSku);

        /** @var \Magento\Bundle\Model\Option $option */
        $option = $this->type->getOptionsCollection($product)->getItemById($optionId);
        if (!$option || !$option->getId()) {
            throw new NoSuchEntityException('Requested option doesn\'t exist');
        }

        $productLinks = $this->linkList->getItems($product, $optionId);

        $this->optionBuilder->populateWithArray($option->getData())
            ->setOptionId($option->getId())
            ->setTitle(is_null($option->getTitle()) ? $option->getDefaultTitle() : $option->getTitle())
            ->setSku($product->getSku())
            ->setProductLinks($productLinks);

        return $this->optionBuilder->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getList($productSku)
    {
        $product = $this->getProduct($productSku);
        return $this->productOptionList->getItems($product);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Bundle\Api\Data\OptionInterface $option)
    {
        try {
            $this->optionResource->delete($option);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\StateException(
                'Cannot delete option with id %option_id',
                ['option_id' => $option->getOptionId()],
                $exception
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($productSku, $optionId)
    {
        $product = $this->getProduct($productSku);
        $optionCollection = $this->type->getOptionsCollection($product);
        $optionCollection->setIdFilter($optionId);
        return $this->delete($optionCollection->getFirstItem());
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Bundle\Api\Data\OptionInterface $option
    ) {
        $option->setStoreId($this->storeManager->getStore()->getId());
        $option->setParentId($product->getId());

        if (!$option->getOptionId()) {
            $option->setDefaultTitle($option->getTitle());
            $linksToAdd = is_array($option->getProductLinks()) ? $option->getProductLinks() : [];
        } else {
            $optionCollection = $this->type->getOptionsCollection($product);
            $optionCollection->setIdFilter($option->getOptionId());

            /** @var \Magento\Bundle\Model\Option $existingOption */
            $existingOption = $optionCollection->getFirstItem();

            if (!$existingOption->getOptionId()) {
                throw new NoSuchEntityException('Requested option doesn\'t exist');
            }

            $option->setOptionId($existingOption->getOptionId());
            $option->setDefaultTitle(is_null($option->getTitle()) ? $existingOption->getTitle() : $option->getTitle());

            /** @var \Magento\Bundle\Api\Data\LinkInterface[] $existingLinks */
            $existingLinks = is_array($existingOption->getProductLinks()) ? $existingOption->getProductLinks() : [];

            /** @var \Magento\Bundle\Api\Data\LinkInterface[] $newProductLinks */
            $newProductLinks = is_array($option->getProductLinks()) ? $option->getProductLinks() : [];

            /** @var \Magento\Bundle\Api\Data\LinkInterface[] $linksToDelete */
            $linksToDelete = array_udiff($existingLinks, $newProductLinks, [$this, 'compareLinks']);
            foreach ($linksToDelete as $link) {
                $this->linkManagement->removeChild($product->getSku(), $option->getOptionId(), $link->getSku());
            }
            /** @var \Magento\Bundle\Api\Data\LinkInterface[] $linksToAdd */
            $linksToAdd = array_udiff($newProductLinks, $existingLinks, [$this, 'compareLinks']);
        }

        try {
            $this->optionResource->save($option);
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not save option', [], $e);
        }

        /** @var \Magento\Bundle\Api\Data\LinkInterface $linkedProduct */
        foreach ($linksToAdd as $linkedProduct) {
            $this->linkManagement->addChild($product, $option->getOptionId(), $linkedProduct);
        }

        return $option->getOptionId();
    }

    /**
     * @param string $productSku
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws Exception
     */
    private function getProduct($productSku)
    {
        $product = $this->productRepository->get($productSku);
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new Exception('Only implemented for bundle product', Exception::HTTP_FORBIDDEN);
        }
        return $product;
    }

    /**
     * Compare two links and determine if they are equal
     *
     * @param \Magento\Bundle\Api\Data\LinkInterface $firstLink
     * @param \Magento\Bundle\Api\Data\LinkInterface $secondLink
     * @return int
     */
    private function compareLinks(
        \Magento\Bundle\Api\Data\LinkInterface $firstLink,
        \Magento\Bundle\Api\Data\LinkInterface $secondLink
    ) {
        if ($firstLink->getSku() == $secondLink->getSku()) {
            return 0;
        } else {
            return 1;
        }
    }
}
