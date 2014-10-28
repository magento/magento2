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
namespace Magento\Wishlist\Model;

class LocaleQuantityProcessor
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Filter\LocalizedToNormalized
     */
    protected $localFilter;

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Filter\LocalizedToNormalized $localFilter
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Filter\LocalizedToNormalized $localFilter
    ) {
        $this->localeResolver = $localeResolver;
        $this->localFilter = $localFilter;
    }

    /**
     * Process localized quantity to internal format
     *
     * @param float $qty
     * @return array|string
     */
    public function process($qty)
    {
        $this->localFilter->setOptions(array('locale' => $this->localeResolver->getLocaleCode()));
        $qty = $this->localFilter->filter((double)$qty);
        if ($qty < 0) {
            $qty = null;
        }
        return $qty;

    }
}
