<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        $tags[] = $this->getTag();
        return parent::save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * Limit the cleaning scope within a tag
     *
     * {@inheritdoc}
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        if ($mode == \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG) {
            $result = false;
            foreach ($tags as $tag) {
                if (parent::clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [$tag, $this->getTag()])) {
                    $result = true;
                }
            }
        } else {
            if ($mode == \Zend_Cache::CLEANING_MODE_ALL) {
                $mode = \Zend_Cache::CLEANING_MODE_MATCHING_TAG;
                $tags = [$this->getTag()];
            } else {
                $tags[] = $this->getTag();
            }
            $result = parent::clean($mode, $tags);
        }
        return $result;
    }
}
