<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Api\ImageContentValidatorInterface;

/**
 * Class GalleryManagement
 *
 * Provides implementation of api interface ProductAttributeMediaGalleryManagementInterface
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GalleryManagement implements \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ImageContentValidatorInterface
     */
    protected $contentValidator;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param ImageContentValidatorInterface $contentValidator
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        ImageContentValidatorInterface $contentValidator
    ) {
        $this->productRepository = $productRepository;
        $this->contentValidator = $contentValidator;
    }

    /**
     * @inheritdoc
     */
    public function create($sku, ProductAttributeMediaGalleryEntryInterface $entry)
    {
        /** @var $entry ProductAttributeMediaGalleryEntryInterface */
        $entryContent = $entry->getContent();

        if (!$this->contentValidator->isValid($entryContent)) {
            throw new InputException(__('The image content is invalid. Verify the content and try again.'));
        }
        $product = $this->productRepository->get($sku, true);

        $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
        $existingEntryIds = [];
        if ($existingMediaGalleryEntries == null) {
            $existingMediaGalleryEntries = [$entry];
        } else {
            foreach ($existingMediaGalleryEntries as $existingEntries) {
                $existingEntryIds[$existingEntries->getId()] = $existingEntries->getId();
            }
            $existingMediaGalleryEntries[] = $entry;
        }
        $product->setMediaGalleryEntries($existingMediaGalleryEntries);
        try {
            $product = $this->productRepository->save($product);
            // phpcs:ignore Magento2.Exceptions.ThrowCatch
        } catch (InputException $inputException) {
            throw $inputException;
            // phpcs:ignore Magento2.Exceptions.ThrowCatch
        } catch (\Exception $e) {
            throw new StateException(__("The product can't be saved."));
        }

        foreach ($product->getMediaGalleryEntries() as $entry) {
            if (!isset($existingEntryIds[$entry->getId()])) {
                return $entry->getId();
            }
        }
        throw new StateException(__('The new media gallery entry failed to save.'));
    }

    /**
     * @inheritdoc
     */
    public function update($sku, ProductAttributeMediaGalleryEntryInterface $entry)
    {
        $product = $this->productRepository->get($sku, true);
        $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
        if ($existingMediaGalleryEntries == null) {
            throw new NoSuchEntityException(
                __('No image with the provided ID was found. Verify the ID and try again.')
            );
        }
        $found = false;
        foreach ($existingMediaGalleryEntries as $key => $existingEntry) {
            $entryTypes = (array)$entry->getTypes();
            $existingEntryTypes = (array)$existingMediaGalleryEntries[$key]->getTypes();
            $existingMediaGalleryEntries[$key]->setTypes(array_diff($existingEntryTypes, $entryTypes));

            if ($existingEntry->getId() == $entry->getId()) {
                $found = true;

                $file = $entry->getContent();

                if ($file && $file->getBase64EncodedData() || $entry->getFile()) {
                    $entry->setId(null);
                }
                $existingMediaGalleryEntries[$key] = $entry;
            }
        }
        if (!$found) {
            throw new NoSuchEntityException(
                __('No image with the provided ID was found. Verify the ID and try again.')
            );
        }
        $product->setMediaGalleryEntries($existingMediaGalleryEntries);

        try {
            $this->productRepository->save($product);
        } catch (\Exception $exception) {
            throw new StateException(__("The product can't be saved."));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function remove($sku, $entryId)
    {
        $product = $this->productRepository->get($sku, true);
        $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
        if ($existingMediaGalleryEntries == null) {
            throw new NoSuchEntityException(
                __('No image with the provided ID was found. Verify the ID and try again.')
            );
        }
        $found = false;
        foreach ($existingMediaGalleryEntries as $key => $entry) {
            if ($entry->getId() == $entryId) {
                unset($existingMediaGalleryEntries[$key]);
                $found = true;
                break;
            }
        }
        if (!$found) {
            throw new NoSuchEntityException(
                __('No image with the provided ID was found. Verify the ID and try again.')
            );
        }
        $product->setMediaGalleryEntries($existingMediaGalleryEntries);
        $this->productRepository->save($product);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function get($sku, $entryId)
    {
        try {
            $product = $this->productRepository->get($sku);
        } catch (\Exception $exception) {
            throw new NoSuchEntityException(__("The product doesn't exist. Verify and try again."));
        }

        $mediaGalleryEntries = $product->getMediaGalleryEntries();
        foreach ($mediaGalleryEntries as $entry) {
            if ($entry->getId() == $entryId) {
                return $entry;
            }
        }

        throw new NoSuchEntityException(__("The image doesn't exist. Verify and try again."));
    }

    /**
     * @inheritdoc
     */
    public function getList($sku)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku);

        return $product->getMediaGalleryEntries();
    }
}
