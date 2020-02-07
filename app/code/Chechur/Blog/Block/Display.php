<?php

declare(strict_types=1);

namespace Chechur\Blog\Block;


use Magento\Framework\View\Element\Template;

class Display extends Template
{

    protected $_postFactory;

    protected $_collection;

    protected $_registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Chechur\Blog\Model\PostFactory $postFactory,
        \Chechur\Blog\Model\ResourceModel\Post\Collection $collection,
        \Magento\Framework\Registry $registry
    )
    {
        $this->_registry = $registry;
        $this->_collection = $collection;
        $this->_postFactory = $postFactory;
        parent::__construct($context);
    }

    public function blog()
    {
        return __('Blog');
    }


    public function getPostCollection()
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

}
