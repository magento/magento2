<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Exception\InputException;

/**
 * Image content validation interface
 *
 * @api
 * @since 2.0.0
 */
interface ImageContentValidatorInterface
{
    /**
     * Check if gallery entry content is valid
     *
     * @param ImageContentInterface $imageContent
     * @return bool
     * @throws InputException
     * @since 2.0.0
     */
    public function isValid(ImageContentInterface $imageContent);
}
