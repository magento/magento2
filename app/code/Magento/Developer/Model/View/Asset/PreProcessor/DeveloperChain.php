<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\View\Asset\PreProcessor;

use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\LocalInterface;

class DeveloperChain extends Chain
{
    /**
     * @param LocalInterface $asset
     * @param string $origContent
     * @param string $origContentType
     * @param string $origAssetPath
     * @codeCoverageIgnore
     */
    public function __construct(
        LocalInterface $asset,
        $origContent,
        $origContentType,
        $origAssetPath
    ) {
        parent::__construct(
            $asset,
            $origContent,
            $origContentType,
            $origAssetPath
        );

        $this->targetContentType = $this->origContentType;
        $this->targetAssetPath = $origAssetPath;
    }
}
