<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Ui;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorComposite;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Url;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\ProductRenderFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\EntityManager\Hydrator;
use Magento\Store\Model\StoreManager;

/**
 * Reports Viewed Products Counter
 *
 * The main responsibility of this class is provide necessary data to track viewed products
 * by customer on frontend and data to synchronize this tracks with backend
 *
 * @api
 * @since 102.0.0
 */
class ProductViewCounter extends Template
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ProductRenderCollectorComposite
     */
    private $productRenderCollectorComposite;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var ProductRenderFactory
     */
    private $productRenderFactory;

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var SerializerInterface
     */
    private $serialize;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Template\Context $context
     * @param ProductRepository $productRepository
     * @param ProductRenderCollectorComposite $productRenderCollectorComposite
     * @param StoreManager $storeManager
     * @param ProductRenderFactory $productRenderFactory
     * @param Hydrator $hydrator
     * @param SerializerInterface $serialize
     * @param Url $url
     * @param Registry $registry
     */
    public function __construct(
        Template\Context $context,
        ProductRepository $productRepository,
        ProductRenderCollectorComposite $productRenderCollectorComposite,
        StoreManager $storeManager,
        ProductRenderFactory $productRenderFactory,
        Hydrator $hydrator,
        SerializerInterface $serialize,
        Url $url,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->productRenderCollectorComposite = $productRenderCollectorComposite;
        $this->storeManager = $storeManager;
        $this->productRenderFactory = $productRenderFactory;
        $this->hydrator = $hydrator;
        $this->serialize = $serialize;
        $this->url = $url;
        $this->registry = $registry;
    }

    /**
     * Calculate item data, that will need to application on frontend
     *
     * Product data calculated on this page, will be cached, for all next web api
     * requests and will be flushed with full page cache
     *
     * @return string {JSON encoded data}
     * @since 102.0.0
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentProductData()
    {
        /** @var ProductInterface $product */
        $product = $this->registry->registry('product');
        /** @var Store $store */
        $store = $this->storeManager->getStore();

        if (!$product || !$product->getId()) {
            return $this->serialize->serialize([
                'items' => [],
                'store' => $store->getId(),
                'currency' => $store->getCurrentCurrency()->getCode()
            ]);
        }

        $productRender = $this->productRenderFactory->create();

        $productRender->setStoreId($store->getId());
        $productRender->setCurrencyCode($store->getCurrentCurrencyCode());
        $this->productRenderCollectorComposite
            ->collect($product, $productRender);
        $data = $this->hydrator->extract($productRender);

        $currentProductData = [
            'items' => [
                $product->getId() => $data
            ],
            'store' => $store->getId(),
            'currency' => $store->getCurrentCurrency()->getCode()
        ];

        return $this->serialize->serialize($currentProductData);
    }
}
