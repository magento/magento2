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
 * @category    Mage
 * @package     Mage_Tag
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tags list in customer's account
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Tag_Block_Customer_Tags extends Mage_Customer_Block_Account_Dashboard
{
    protected $_tags;
    protected $_minPopularity;
    protected $_maxPopularity;

    protected function _loadTags()
    {
        if (empty($this->_tags)) {
            $this->_tags = array();

            $tags = Mage::getResourceModel('Mage_Tag_Model_Resource_Tag_Collection')
                ->addPopularity(null, Mage::app()->getStore()->getId())
                ->setOrder('popularity', 'DESC')
                ->addCustomerFilter(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId())
                ->setActiveFilter()
                ->load()
                ->getItems();
        } else {
            return;
        }

        if( isset($tags) && count($tags) == 0 ) {
            return;
        }

        $this->_maxPopularity = reset($tags)->getPopularity();
        $this->_minPopularity = end($tags)->getPopularity();
        $range = $this->_maxPopularity - $this->_minPopularity;
        $range = ( $range == 0 ) ? 1 : $range;

        foreach ($tags as $tag) {
            $tag->setRatio(($tag->getPopularity()-$this->_minPopularity)/$range);
            $this->_tags[$tag->getName()] = $tag;
        }
        ksort($this->_tags);
    }

    public function getTags()
    {
        $this->_loadTags();
        return $this->_tags;
    }

    public function getMaxPopularity()
    {
        return $this->_maxPopularity;
    }

    public function getMinPopularity()
    {
        return $this->_minPopularity;
    }
}
