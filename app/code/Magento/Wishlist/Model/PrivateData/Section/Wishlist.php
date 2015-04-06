<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Model\PrivateData\Section;

use Magento\Customer\Model\PrivateData\Section\SectionSourceInterface;

/**
 * Wishlist section
 */
class Wishlist implements SectionSourceInterface
{
    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     */
    public function __construct(\Magento\Wishlist\Helper\Data $wishlistHelper)
    {
        $this->wishlistHelper = $wishlistHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        return [
            'counter' => $this->getCounter(),
        ];
    }

    /**
     * @return string
     */
    public function getCounter()
    {
        return $this->createCounter($this->getItemCount());
    }

    /**
     * Count items in wishlist
     *
     * @return int
     */
    protected function getItemCount()
    {
        return $this->wishlistHelper->getItemCount();
    }

    /**
     * Create button label based on wishlist item quantity
     *
     * @param int $count
     * @return \Magento\Framework\Phrase|null
     */
    protected function createCounter($count)
    {
        if ($count > 1) {
            return __('%1 items', $count);
        } elseif ($count == 1) {
            return __('1 item');
        }
        return null;
    }
}
