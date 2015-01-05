<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\CatalogSearch\Model\Layer\Search;

use Magento\Catalog\Model\Layer\StateKeyInterface;

class StateKey extends \Magento\Catalog\Model\Layer\Category\StateKey implements StateKeyInterface
{
    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Search\Model\QueryFactory $queryFactory
    ) {
        $this->queryFactory = $queryFactory;
        parent::__construct($storeManager, $customerSession);
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return string|void
     */
    public function toString($category)
    {
        return 'Q_' . $this->queryFactory->get()->getId()
        . '_' . \Magento\Catalog\Model\Layer\Category\StateKey::toString($category);
    }
}
