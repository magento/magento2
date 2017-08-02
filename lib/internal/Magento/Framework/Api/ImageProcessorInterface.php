<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Exception\InputException;

/**
 * Interface ImageProcessorInterface
 *
 * @api
 * @since 2.0.0
 */
interface ImageProcessorInterface
{
    /**
     * Process Data objects with image type custom attributes and update the custom attribute values with saved image
     * paths
     *
     * @api
     * @param CustomAttributesDataInterface $dataObjectWithCustomAttributes
     * @param string $entityType entity type
     * @param CustomAttributesDataInterface $previousCustomerData
     * @return CustomAttributesDataInterface
     * @since 2.0.0
     */
    public function save(
        CustomAttributesDataInterface $dataObjectWithCustomAttributes,
        $entityType,
        CustomAttributesDataInterface $previousCustomerData = null
    );

    /**
     * Process image and save it to the entity's media directory
     *
     * @param string $entityType
     * @param ImageContentInterface $imageContent
     * @return string Relative path of the file where image was saved
     * @throws InputException
     * @since 2.0.0
     */
    public function processImageContent($entityType, $imageContent);
}
