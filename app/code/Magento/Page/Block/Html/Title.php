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
 * Template title block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Page\Block\Html;

class Title extends \Magento\Core\Block\Template
{
    /**
     * Own page title to display on the page
     *
     * @var string
     */
    protected $_pageTitle;

    /**
     * Provide own page title or pick it from Head Block
     *
     * @return string
     */
    public function getPageTitle()
    {
        if (!empty($this->_pageTitle)) {
            return $this->_pageTitle;
        }
        return $this->getLayout()->getBlock('head')->getShortTitle();
    }

    /**
     * Set own page title
     *
     * @param $pageTitle
     */
    public function setPageTitle($pageTitle)
    {
        $this->_pageTitle = $pageTitle;
    }
}
