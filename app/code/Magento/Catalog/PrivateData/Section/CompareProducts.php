<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\PrivateData\Section;

use Magento\Customer\Model\PrivateData\Section\SectionSourceInterface;

class CompareProducts implements SectionSourceInterface
{
    /** @var \Magento\Catalog\Helper\Product\Compare */
    protected $helper;

    /**
     * @param \Magento\Catalog\Helper\Product\Compare $helper
     */
    public function __construct(
        \Magento\Catalog\Helper\Product\Compare $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $count = $this->helper->getItemCount();
        return [
            'count' => $count,
            'countCaption' => $count == 1 ? __('1 item') : __('%1 items', $count),
            'listUrl' => $this->helper->getListUrl(),
        ];
    }
}
