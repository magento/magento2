<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sniffs\Annotation\_files;

/**
 * Class for method structure for annotations test cases
 */
class MethodAnnotationFixture
{
    /**
     *
     * @inheritdoc
     */
    public function getProductListDefaultSortBy1()
    {
    }

    /**
     *
     * @inheritdoc
     */
    public function getProductListDefaultSortBy10($store = null)
    {
        return $store;
    }

    /**
     * Block for short
     *
     * {@inheritdoc}
     *
     */
    public function getProductListDefaultSortBy102()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getProductListDefaultBy()
    {
        return;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function getProductListDefaultSortBy13()
    {
        return;
    }

    /**
     * ProductVisibilityCondition constructor
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     */
    public function content(\Magento\Catalog\Model\Product\Visibility $productVisibility)
    {
        $this->productVisibility = $productVisibility;
    }

    /**
     * block description
     *
     * {@inheritdoc}
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    public function construct(AbstractDb $collection)
    {
        /** @var */
        $collection->setVisibility($this->productVisibility->getVisibleInCatalogIds());
    }

    /**
     * Move category
     *
     *
     * @param int $parentId new parent category id
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function move($parentId)
    {
        /**
         * Validate new parent category id. (category model is used for backward
         * compatibility in event params)
         */
        try {
            $this->categoryRepository->get($parentId, $this->getStoreId());
        } catch (NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Sorry, but we can\'t find the new parent category you selected.'),
                $e
            );
        }
        return true;
    }

    /**
     * Block for short description
     *
     * This a long description {@inheritdoc} consists more lines as part of the long description
     * on multi line.
     *
     * @param int $store
     *
     *
     */
    public function getProductListDefaultSortBy26032($store)
    {
        return $store;
    }

    /**
     *
     *
     *
     */
    public function getProductListDefaultSortBy2632()
    {
    }

    /**
     * Block for short description
     *
     * This a long description {@inheritdoc} consists more lines as part of the long description
     * on multi line.
     *
     * @param int $store
     *
     *
     *
     */
    public function getProductListDefaultSortBy2002($store)
    {
        return $store;
    }

    /**
     *
     * block for short description
     *
     * @param int $store
     * @return int
     */
    public function getProductListDefaultSortBy3002($store)
    {
        return $store;
    }

    /**
     * Block for short description
     *
     * @see consists more lines as part of the long description
     * on multi line.
     *
     * @param string $store
     * @param string $foo
     */
    public function getProductListDefaultSortBy12($store, $foo)
    {
        return $store === $foo;
    }

    /**
     * Block for short description
     *
     * {@inheritdoc}
     *
     * @param string $store
     * @param string $foo
     */
    public function getProductListDefaultSort2($store, $foo)
    {
        return $store === $foo;
    }

    /**
     * Block for short description
     *
     * a long description {@inheritdoc} consists more lines as part of the long description
     * on multi line.
     *
     * @param string $store
     * @param string $foo
     */
    public function getProductListDefault($store, $foo)
    {
        return $store === $foo;
    }

    /**
     * Retrieve custom options
     *
     * @param ProductOptionInterface $productOption
     *
     * @return array
     */
    protected function getCustomOptions(ProductOptionInterface $productOption)
    {
        if ($productOption
            && $productOption->getExtensionAttributes()
            && $productOption->getExtensionAttributes()->getCustomOptions()
        ) {
            return $productOption->getExtensionAttributes()
                ->getCustomOptions();
        }
        return [];
    }

    /**
     * This is the summary for a DocBlock.
     *
     * This is the description for a DocBlock. This text may contain
     * multiple lines and even some _markdown_.
     * * Markdown style lists function too
     * * Just try this out once
     * The section after the description contains the tags; which provide
     * structured meta-data concerning the given element.
     *
     * @param int $example  This is an example function/method parameter description.
     * @param string $example2 This is a second example.
     *
     */
    public function getProductListDefaultSortBy2($example, $example2)
    {
        return $example === $example2;
    }

    /**
     * Returns the content of the tokens from the specified start position in
     * the token stack for the specified length.
     *
     * @param int $start
     * @param int $length
     *
     * @return string The token contents.
     */
    public function getProductListDefaultSortBy($start, $length)
    {
        return $start === $length;
    }

    /**
     * Some text about this step/method returns the content of the tokens the token stack for the specified length
     *
     * @param string $name
     * @param string $folder
     *
     * @see this file
     * @When I create a file called :name in :folder
     */
    public function getProductListDefaultSortBy222($name, $folder)
    {
        return $name === $folder;
    }

    public function setExtensionAs(\Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     *
     * short description
     * @param \Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes
     * @return mixed
     */
    public function setEn(\Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @param \Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes
     * @return mixed
     */
    public function setExtenw(\Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     *
     * Short description
     * @param \Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes
     * @return mixed
     */
    public function setExff(\Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * {@inheritdoc}
     *
     * @param int $start
     * @param int $length
     *
     * @return string The token contents.
     */
    public function getProductSortBy($start, $length)
    {
        return $start === $length;
    }
}
