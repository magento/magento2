<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\QueryFactory;

/**
 * Block for logging search terms on cached pages
 *
 * @api
 */
class SearchTermsLog extends Template
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * @param Context $context
     * @param QueryFactory $queryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        \Magento\Framework\App\ResponseInterface $response,
        array $data = []
    ) {
        $this->queryFactory = $queryFactory;
        $this->response = $response;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve query model object
     *
     * @return \Magento\Search\Model\Query
     */
    public function getQuery()
    {
        return $this->queryFactory->get();
    }

    /**
     * Insert ajax block for logging search terms on cached pages
     *
     * @return bool
     */
    public function isAjaxInsert()
    {
        $pragma = $this->response->getHeader('pragma')->getFieldValue();
        return ($pragma == 'cache');
    }
}
