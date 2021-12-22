<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Wrapper for native iptcembed function
 */
class IptcEmbed
{
    /**
     * Returns result of iptcembed function
     *
     * @param string $iptcData
     * @param string $filePath
     * @throws LocalizedException if iptcembed function is not enabled
     */
    public function get(string $iptcData, string $filePath)
    {
        if (!is_callable('iptcembed')) {
            throw new LocalizedException(__('iptcembed() must be enabled in php configuration'));
        }

        return iptcembed($iptcData, $filePath);
    }
}
