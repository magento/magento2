<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Model\Plugin\ProductList;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Catalog\Model\Product\ProductList\CollectionLoader as Loader;
use Magento\Review\Model\ReviewFactory;

/**
 * Class CollectionLoader
 *
 * @api
 * @since 100.0.2
 */
class CollectionLoader
{
    /**
     * Review model
     *
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @param ReviewFactory $reviewFactory
     */
    public function __construct(ReviewFactory $reviewFactory)
    {
        $this->reviewFactory = $reviewFactory;
    }

    /**
     * Append review summary before rendering html
     *
     * @param Loader $subject
     * @param AbstractCollection $result
     * @return AbstractCollection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoad(
        Loader $subject,
        AbstractCollection $result
    ) {
        $this->reviewFactory->create()->appendSummary($result);
        return $result;
    }
}
