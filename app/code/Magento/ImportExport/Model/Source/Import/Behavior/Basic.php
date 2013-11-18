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
 * @category    Magento
 * @package     Magento_ImportExport
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Import behavior source model used in import for product and customer import entities.
 * Source model saved to maintain compatibility with Magento 1.* import.
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ImportExport\Model\Source\Import\Behavior;

class Basic
    extends \Magento\ImportExport\Model\Source\Import\AbstractBehavior
{
    /**
     * Get possible behaviours
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND
                => __('Append Complex Data'),
            \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE
                => __('Replace Existing Complex Data'),
            \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE
                => __('Delete Entities'),
        );
    }

    /**
     * Get current behaviour code
     *
     * @return string
     */
    public function getCode()
    {
        return 'basic';
    }
}
