<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Translate;

/**
 * Returns the translation resource data.
 *
 * @api
 */
interface ResourceInterface
{
    /**
     * Retrieve translation array for store / locale code
     *
     * @param int $scope
     * @param string $locale
     * @return array
     */
    public function getTranslationArray($scope = null, $locale = null);
}
