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
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Model;

/**
 * @method \Magento\Checkout\Model\Resource\Agreement _getResource()
 * @method \Magento\Checkout\Model\Resource\Agreement getResource()
 * @method string getName()
 * @method \Magento\Checkout\Model\Agreement setName(string $value)
 * @method string getContent()
 * @method \Magento\Checkout\Model\Agreement setContent(string $value)
 * @method string getContentHeight()
 * @method \Magento\Checkout\Model\Agreement setContentHeight(string $value)
 * @method string getCheckboxText()
 * @method \Magento\Checkout\Model\Agreement setCheckboxText(string $value)
 * @method int getIsActive()
 * @method \Magento\Checkout\Model\Agreement setIsActive(int $value)
 * @method int getIsHtml()
 * @method \Magento\Checkout\Model\Agreement setIsHtml(int $value)
 *
 * @category    Magento
 * @package     Magento_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Agreement extends \Magento\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Checkout\Model\Resource\Agreement');
    }
}
