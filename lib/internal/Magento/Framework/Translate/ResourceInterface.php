<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Translate;

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
