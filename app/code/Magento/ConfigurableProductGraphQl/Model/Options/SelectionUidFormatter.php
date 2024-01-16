<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProductGraphQl\Model\Options;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Uid;

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
     * @var Uid
     */
    private $idEncoder;

    /**
     * @param Uid $idEncoder
     */
    public function __construct(Uid $idEncoder)
    {
        $this->idEncoder = $idEncoder;
    }

    /**
     * Create uid and encode.
     *
     * @param int $attributeId
     * @param int $indexId
     * @return string
     */
    public function encode(int $attributeId, int $indexId): string
    {
        return $this->idEncoder->encode(implode(self::UID_SEPARATOR, [self::UID_PREFIX, $attributeId, $indexId]));
    }

    /**
     * Retrieve attribute and option index from uid. Array key is the id of attribute and value is the index of option
     *
     * @param array $selectionUids
     * @return array
     * @throws GraphQlInputException
     */
    public function extract(array $selectionUids): array
    {
        $attributeOption = [];
        foreach ($selectionUids as $uid) {
            $decodedUid = $this->idEncoder->decode($uid);
            $optionData = $decodedUid !== null ? explode(self::UID_SEPARATOR, $decodedUid) : [];
            if (count($optionData) === 3) {
                $attributeOption[(int)$optionData[1]]  = (int)$optionData[2];
            }
        }

        return $attributeOption;
    }
}
