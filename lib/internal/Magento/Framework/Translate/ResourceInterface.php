<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Translate;

/**
 * Returns the translation resource data.
 *
 * @api
 * @since 2.0.0
 */
interface ResourceInterface
{
    /**
     * Retrieve translation array for store / locale code
     *
     * @param int $scope
     * @param string $locale
     * @return array
     * @since 2.0.0
     */
    public function getTranslationArray($scope = null, $locale = null);
}
