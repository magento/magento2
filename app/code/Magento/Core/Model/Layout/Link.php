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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layout Link model class
 *
 * @method int getStoreId()
 * @method int getThemeId()
 * @method int getLayoutUpdateId()
 * @method \Magento\Core\Model\Layout\Link setStoreId($id)
 * @method \Magento\Core\Model\Layout\Link setThemeId($id)
 * @method \Magento\Core\Model\Layout\Link setLayoutUpdateId($id)
 */
namespace Magento\Core\Model\Layout;

class Link extends \Magento\Core\Model\AbstractModel
{
    /**
     * Layout Update model initialization
     */
    protected function _construct()
    {
        $this->_init('Magento\Core\Model\Resource\Layout\Link');
    }
}
