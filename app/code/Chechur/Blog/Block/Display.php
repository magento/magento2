<?php
declare(strict_types=1);

namespace Chechur\Blog\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class Display extends Template
{
    /**
     * @var \Chechur\Blog\Model\PostFactory
     */
    protected $_postFactory;

    /**\
     * @var \Chechur\Blog\Model\ResourceModel\Post\Collection
     */
    protected $_collection;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Display constructor.
     * @param Template\Context $context
     * @param \Chechur\Blog\Model\PostFactory $postFactory
     * @param \Chechur\Blog\Model\ResourceModel\Post\Collection $collection
     * @param \Magento\Framework\Registry $registry
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Chechur\Blog\Model\PostFactory $postFactory,
        \Chechur\Blog\Model\ResourceModel\Post\Collection $collection,
        \Magento\Framework\Registry $registry,
        StoreManagerInterface $storeManager
    )
    {
        $this->_storeManager = $storeManager;
        $this->_registry = $registry;
        $this->_collection = $collection;
        $this->_postFactory = $postFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function blog(): string
    {
        return (string)__('Blog');
    }

    /**
     * Get array of Collection
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getPostCollection(): array
    {

        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $this->_registry->registry('current_product');

        if ($product) {
            $configTypeOfProduct = $this->_collection->getTypeOfVisible();
            $productTypeId = $product->getTypeId();
            if (in_array($productTypeId, $configTypeOfProduct)) {
                $post = $this->_postFactory->create();
                return $post->getCollection()->addFieldToFilter('type', array('eq' => $productTypeId))
                    ->setOrder('created_at', 'ASC')->setPageSize(5);
            }
        }
    }

    /**
     * @param $image
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImageUrl($image)
    {
        $mediaUrl = $this->_storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $imageUrl = $mediaUrl . 'post/tmp/image/' . $image;
        return $imageUrl;
    }

}
