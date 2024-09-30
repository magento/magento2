<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProductGraphQl\Model\Options;

/**
 * Handle option selection uid.
 */
class SelectionUidFormatter
{
    /**
     * Prefix of uid for encoding
     */
    private const UID_PREFIX = 'configurable';

    /**
     * Separator of uid for encoding
     */
    private const UID_SEPARATOR = '/';

    /**
     * Create uid and encode.
     *
     * @param int $attributeId
     * @param int $indexId
     * @return string
     */
    public function encode(int $attributeId, int $indexId): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return base64_encode(implode(self::UID_SEPARATOR, [
            self::UID_PREFIX,
            $attributeId,
            $indexId
        ]));
    }

    /**
     * Retrieve attribute and option index from uid. Array key is the id of attribute and value is the index of option
     *
     * @param string $selectionUids
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function extract(array $selectionUids): array
    {
        $attributeOption = [];
        foreach ($selectionUids as $uid) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $optionData = explode(self::UID_SEPARATOR, base64_decode($uid));
            if (count($optionData) == 3) {
                $attributeOption[(int)$optionData[1]]  = (int)$optionData[2];
            }
        }

        return $attributeOption;
    }
}
