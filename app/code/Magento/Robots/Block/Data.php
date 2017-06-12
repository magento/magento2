<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Block;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Robots\Model\Config\Value;
use Magento\Robots\Model\Robots;
use Magento\Store\Model\StoreResolver;

/**
 * Robots Block Class.
 * Prepares base content for robots.txt and implements Page Cache functionality.
 *
 * @api
 */
class Data extends AbstractBlock implements IdentityInterface
{
    /**
     * @var Robots
     */
    private $robots;
    /**
     * @var StoreResolver
     */
    private $storeResolver;

    /**
     * @param Context $context
     * @param Robots $robots
     * @param StoreResolver $storeResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        Robots $robots,
        StoreResolver $storeResolver,
        array $data = []
    ) {
        $this->robots = $robots;
        $this->storeResolver = $storeResolver;

        parent::__construct($context, $data);
    }

    /**
     * Prepare base content for robots.txt file
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->robots->getData();
    }

    /**
     * Get unique page cache identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [
            Value::CACHE_TAG . '_' . $this->storeResolver->getCurrentStoreId(),
        ];
    }
}
