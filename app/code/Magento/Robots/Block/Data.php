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
 * @since 2.2.0
 */
class Data extends AbstractBlock implements IdentityInterface
{
    /**
     * @var Robots
     * @since 2.2.0
     */
    private $robots;

    /**
     * @var StoreResolver
     * @since 2.2.0
     */
    private $storeResolver;

    /**
     * @param Context $context
     * @param Robots $robots
     * @param StoreResolver $storeResolver
     * @param array $data
     * @since 2.2.0
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
     * Retrieve base content for robots.txt file
     *
     * @return string
     * @since 2.2.0
     */
    protected function _toHtml()
    {
        return $this->robots->getData() . PHP_EOL;
    }

    /**
     * Get unique page cache identities
     *
     * @return array
     * @since 2.2.0
     */
    public function getIdentities()
    {
        return [
            Value::CACHE_TAG . '_' . $this->storeResolver->getCurrentStoreId(),
        ];
    }
}
