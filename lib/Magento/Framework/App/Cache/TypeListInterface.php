<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App\Cache;

interface TypeListInterface
{
    /**
     * Get information about all declared cache types
     *
     * @return array
     */
    public function getTypes();

    /**
     * Get array of all invalidated cache types
     *
     * @return array
     */
    public function getInvalidated();

    /**
     * Mark specific cache type(s) as invalidated
     *
     * @param string|array $typeCode
     * @return void
     */
    public function invalidate($typeCode);

    /**
     * Clean cached data for specific cache type
     *
     * @param string $typeCode
     * @return void
     */
    public function cleanType($typeCode);
}
