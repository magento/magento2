<?php
/**
 *
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
namespace Magento\Checkout\Service\V1\Cart;

use Magento\Checkout\Service\V1\Data\Cart;
use Magento\Sales\Model\Quote;
use Magento\Sales\Model\QuoteRepository;
use \Magento\Checkout\Service\V1\Data\Cart\Totals;

class TotalsService implements TotalsServiceInterface
{
    /**
     * @var Cart\TotalsBuilder
     */
    private $totalsBuilder;

    /**
     * @var Cart\TotalsMapper
     */
    private $totalsMapper;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var Totals\ItemMapper;
     */
    private $itemTotalsMapper;

    /**
     * @param Cart\TotalsBuilder $totalsBuilder
     * @param Cart\TotalsMapper $totalsMapper
     * @param QuoteRepository $quoteRepository
     * @param Totals\ItemMapper $itemTotalsMapper
     */
    public function __construct(
        Cart\TotalsBuilder $totalsBuilder,
        Cart\TotalsMapper $totalsMapper,
        QuoteRepository $quoteRepository,
        Totals\ItemMapper $itemTotalsMapper
    ) {
        $this->totalsBuilder = $totalsBuilder;
        $this->totalsMapper = $totalsMapper;
        $this->quoteRepository = $quoteRepository;
        $this->itemTotalsMapper = $itemTotalsMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotals($cartId)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);

        $this->totalsBuilder->populateWithArray($this->totalsMapper->map($quote));
        $items = [];
        foreach ($quote->getAllItems() as $item) {
            $items[] = $this->itemTotalsMapper->extractDto($item);
        }
        $this->totalsBuilder->setItems($items);

        return $this->totalsBuilder->create();
    }
}
