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

namespace Magento\Catalog\Service\V1\Product\Attribute\Media;

use Magento\Catalog\Service\V1\Product\Attribute\Media\Data\MediaImageBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use \Magento\Catalog\Service\V1\Product\Attribute\Media\Data\GalleryEntryBuilder;
use \Magento\Catalog\Model\Product;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReadService implements ReadServiceInterface
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $setFactory;

    /** @var \Magento\Catalog\Service\V1\Product\Attribute\Media\Data\MediaImageBuilder */
    protected $builder;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var GalleryEntryBuilder
     */
    protected $galleryEntryBuilder;
    
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media
     */
    protected $mediaGallery;

    /**
     * @var \Magento\Catalog\Model\Resource\Eav\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $mediaGallery
     * @param \Magento\Catalog\Model\Resource\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param MediaImageBuilder $mediaImageBuilder
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param GalleryEntryBuilder $galleryEntryBuilder
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $mediaGallery,
        \Magento\Catalog\Model\Resource\Eav\AttributeFactory $attributeFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        MediaImageBuilder $mediaImageBuilder,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        GalleryEntryBuilder $galleryEntryBuilder
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->setFactory = $setFactory;
        $this->eavConfig = $eavConfig;
        $this->mediaGallery = $mediaGallery;
        $this->attributeFactory = $attributeFactory;
        $this->storeManager = $storeManager;
        $this->builder = $mediaImageBuilder;
        $this->productRepository = $productRepository;
        $this->galleryEntryBuilder = $galleryEntryBuilder;
    }

    /**
     * Convert data from array to data object
     *
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute[] $items
     * @return array|\Magento\Catalog\Model\Resource\Eav\Attribute[]
     * @throws \Magento\Framework\Exception\StateException
     */
    protected function prepareData($items)
    {
        $data = [];
        /** @var \Magento\Catalog\Model\Resource\Eav\Attribute $attribute */
        foreach ($items as $attribute) {
            $this->builder->setFrontendLabel($attribute->getStoreLabel());
            $this->builder->setCode($attribute->getData('attribute_code'));
            $this->builder->setIsUserDefined($attribute->getData('is_user_defined'));
            if ($attribute->getIsGlobal()) {
                $scope = 'Global';
            } elseif ($attribute->isScopeWebsite()) {
                $scope = 'Website';
            } elseif ($attribute->isScopeStore()) {
                $scope = 'Store View';
            } else {
                throw new StateException('Attribute has invalid scope. Id = ' . $attribute->getId());
            }
            $this->builder->setScope($scope);
            $data[] = $this->builder->create();
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function types($attributeSetId)
    {
        $attributeSet = $this->setFactory->create()->load($attributeSetId);
        if (!$attributeSet->getId()) {
            throw NoSuchEntityException::singleField('attribute_set_id', $attributeSetId);
        }

        $productEntityId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
        if ($attributeSet->getEntityTypeId() != $productEntityId) {
            throw InputException::invalidFieldValue('entity_type_id', $attributeSetId);
        }

        $collection = $this->collectionFactory->create();
        $collection->setAttributeSetFilter($attributeSetId);
        $collection->setFrontendInputTypeFilter('media_image');
        $collection->addStoreLabel($this->storeManager->getStore()->getId());

        return $this->prepareData($collection->getItems());
    }

    /**
     * {@inheritdoc}
     */
    public function getList($productSku)
    {
        $result = array();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($productSku);

        $galleryAttribute = $this->attributeFactory->create()->loadByCode(
            $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY),
            'media_gallery'
        );

        $container = new \Magento\Framework\Object(array('attribute' => $galleryAttribute));
        $gallery = $this->mediaGallery->loadGallery($product, $container);

        $productImages = $this->getMediaAttributeValues($product);

        foreach ($gallery as $image) {
            $this->galleryEntryBuilder->setId($image['value_id']);
            $this->galleryEntryBuilder->setLabel($image['label_default']);
            $this->galleryEntryBuilder->setTypes(array_keys($productImages, $image['file']));
            $this->galleryEntryBuilder->setDisabled($image['disabled_default']);
            $this->galleryEntryBuilder->setPosition($image['position_default']);
            $this->galleryEntryBuilder->setFile($image['file']);
            $result[] = $this->galleryEntryBuilder->create();
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function info($productSku, $imageId)
    {
        try {
            $product = $this->productRepository->get($productSku);
        } catch (\Exception $exception) {
            throw new NoSuchEntityException("Such product doesn't exist");
        }

        $output = null;
        $productImages = $this->getMediaAttributeValues($product);
        foreach ((array)$product->getMediaGallery('images') as $image) {
            if (intval($image['value_id']) == intval($imageId)) {
                $image['types'] = array_keys($productImages, $image['file']);
                $output = $this->galleryEntryBuilder->populateWithArray($image)->create();
                break;
            }
        }

        if (is_null($output)) {
            throw new NoSuchEntityException("Such image doesn't exist");
        }
        return $output;
    }

    /**
     * Retrieve assoc array that contains media attribute values of the given product
     *
     * @param Product $product
     * @return array
     */
    protected function getMediaAttributeValues(Product $product)
    {
        $mediaAttributeCodes = array_keys($product->getMediaAttributes());
        $mediaAttributeValues = array();
        foreach ($mediaAttributeCodes as $attributeCode) {
            $mediaAttributeValues[$attributeCode] = $product->getData($attributeCode);
        }
        return $mediaAttributeValues;
    }
}
