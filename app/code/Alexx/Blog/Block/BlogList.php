<?php

namespace Alexx\Blog\Block;

use Alexx\Blog\Model\BlogPostsFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;

/**
 * CategoryList  Block  Template
 */
class BlogList extends Template
{
    const XML_PATH_BLOG_VISIBLE = 'catalog_blog/general/applied_to';

    protected $_scopeConfig;
    private $_blogsFactory;
    private $_currentProduct;

    /**
     * Constructor
     *
     * @param Context $context
     * @param BlogPostsFactory $blogsFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     * */
    public function __construct(
        Context $context,
        BlogPostsFactory $blogsFactory,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $objectManager = ObjectManager::getInstance();
        //get current product
        $product = $objectManager->get(Registry::class)->registry('current_product');

        $this->_currentProduct = $product;
        $this->_scopeConfig = $scopeConfig;
        $this->_blogsFactory = $blogsFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get Product Type Id
     * */
    public function getCurrentProductTypeId()
    {
        return $this->_currentProduct->getTypeId();
    }

    /**
     * Get Product Id
     * */
    public function getCurrentProductId()
    {
        return $this->_currentProduct->getId();
    }

    /**
     * Gets system config value for checking applying blog to current product type
     *
     * @return string
     * */
    private function getBlogSettingIsApplied()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_BLOG_VISIBLE);
    }

    /**
     * Checking all paramerets
     *
     * @return bool
     * */
    public function isBlogIsEnabled()
    {
        return in_array($this->getCurrentProductTypeId(), explode(',', $this->getBlogSettingIsApplied()));
    }

    /**
     * Gets latest blog posts
     * */
    public function getPosts()
    {
        return $this->_blogsFactory->create()->getLatestPosts();
    }
}
