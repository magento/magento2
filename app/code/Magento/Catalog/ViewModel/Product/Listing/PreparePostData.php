<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\ViewModel\Product\Listing;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Data\Helper\PostHelper;

/**
 * Check is available add to compare.
 */
class PreparePostData implements ArgumentInterface
{
    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @param PostHelper $postHelper
     */
    public function __construct(PostHelper $postHelper)
    {
        $this->postHelper = $postHelper;
    }

    /**
     * get data for post by javascript in format acceptable to $.mage.dataPost widget
     *
     * @param string $url
     * @param array $data
     * @return string
     */
    public function getPostData(string $url, array $data = []):string
    {
        return (string) $this->postHelper->getPostData($url, $data);
    }
}
