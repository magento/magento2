<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Block\Catalog\Product\View;

use Magento\Mtf\Block\Block;

/**
 * Class Samples
 *
 * Downloadable samples blocks on frontend
 */
class Samples extends Block
{
    /**
     * Title selector for samples block
     *
     * @var string
     */
    protected $titleBlock = '.item-title';

    /**
     * Title selector item sample link
     *
     * @var string
     */
    protected $linkTitle = '.item-link';

    /**
     * Get title for Samples block
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_rootElement->find($this->titleBlock)->getText();
    }

    /**
     * Get sample links
     *
     * @return array
     */
    public function getLinks()
    {
        $links = $this->_rootElement->getElements($this->linkTitle);
        $linksData = [];

        foreach ($links as $link) {
            $linksData[] = [
                'title' => $link->getText(),
            ];
        }

        return $linksData;
    }
}
