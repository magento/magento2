<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Checkout\Service\V1\Item;

use Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Checkout\Service\V1\Data\Cart\ItemBuilder as ItemBuilder;
use \Magento\Checkout\Service\V1\Data\Cart\Item as Item;

class ReadService implements ReadServiceInterface
{
    /**
     * @var \Magento\Checkout\Service\V1\QuoteLoader
     */
    protected $quoteLoader;

    /**
     * @var ItemBuilder
     */
    protected $itemBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Checkout\Service\V1\QuoteLoader $quoteLoader
     * @param ItemBuilder $itemBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Checkout\Service\V1\QuoteLoader $quoteLoader,
        ItemBuilder $itemBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->quoteLoader = $quoteLoader;
        $this->itemBuilder = $itemBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($cartId)
    {
        $output = [];
        $storeId = $this->storeManager->getStore()->getId();
        /** @var  \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteLoader->load($cartId, $storeId);

        /** @var  \Magento\Sales\Model\Quote\Item  $item */
        foreach ($quote->getAllItems() as $item) {
            $data = [
                Item::SKU => $item->getSku(),
                Item::NAME => $item->getName(),
                Item::PRICE => $item->getPrice(),
                Item::QTY => $item->getQty(),
                Item::TYPE => $item->getProductType()
            ];

            $output[] = $this->itemBuilder->populateWithArray($data)->create();
        }
        return $output;
    }
}
