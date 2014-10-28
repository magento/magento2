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

/**
 * Cache frontend decorator that limits the cleaning scope within a tag
 */
namespace Magento\Framework\Cache\Frontend\Decorator;

class TagScope extends \Magento\Framework\Cache\Frontend\Decorator\Bare
{
    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    private $_tag;

    /**
     * @param \Magento\Framework\Cache\FrontendInterface $frontend
     * @param string $tag Cache tag name
     */
    public function __construct(\Magento\Framework\Cache\FrontendInterface $frontend, $tag)
    {
        parent::__construct($frontend);
        $this->_tag = $tag;
    }

    /**
     * Retrieve cache tag name
     *
     * @return string
     */
    public function getTag()
    {
        return $this->_tag;
    }

    /**
     * Enforce marking with a tag
     *
     * {@inheritdoc}
     */
    public function save($data, $identifier, array $tags = array(), $lifeTime = null)
    {
        $tags[] = $this->_tag;
        return parent::save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * Limit the cleaning scope within a tag
     *
     * {@inheritdoc}
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = array())
    {
        if ($mode == \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG) {
            $result = false;
            foreach ($tags as $tag) {
                if (parent::clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($tag, $this->_tag))) {
                    $result = true;
                }
            }
        } else {
            if ($mode == \Zend_Cache::CLEANING_MODE_ALL) {
                $mode = \Zend_Cache::CLEANING_MODE_MATCHING_TAG;
                $tags = array($this->_tag);
            } else {
                $tags[] = $this->_tag;
            }
            $result = parent::clean($mode, $tags);
        }
        return $result;
    }
}
