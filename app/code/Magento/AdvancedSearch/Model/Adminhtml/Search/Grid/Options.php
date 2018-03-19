<?php
/**
 * Options for Query Id column
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Adminhtml\Search\Grid;

/**
 * @api
 * @since 100.0.2
 */
class Options implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registryManager;

    /**
     * @var \Magento\AdvancedSearch\Model\ResourceModel\Recommendations $_searchResourceModel
     */
    protected $_searchResourceModel;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\AdvancedSearch\Model\ResourceModel\Recommendations $searchResourceModel
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\AdvancedSearch\Model\ResourceModel\Recommendations $searchResourceModel
    ) {
        $this->_request = $request;
        $this->_registryManager = $registry;
        $this->_searchResourceModel = $searchResourceModel;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $queries = $this->_request->getPost('selected_queries');

        $currentQueryId = $this->_registryManager->registry('current_catalog_search')->getId();
        $queryIds = [];
        if ($queries === null && !empty($currentQueryId)) {
            $queryIds = $this->_searchResourceModel->getRelatedQueries($currentQueryId);
        }
        return $queryIds;
    }
}
