<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Block\Controller;

use Magento\Framework\View\Block\IdentityInterface;
use Magento\Framework\View\Element\AbstractBlock;

class StubBlock extends AbstractBlock implements IdentityInterface
{
    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return ['identity1', 'identity2'];
    }
}
