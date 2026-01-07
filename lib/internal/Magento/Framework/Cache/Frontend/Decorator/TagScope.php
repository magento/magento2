<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Decorator;

use Magento\Framework\Cache\CacheConstants;

/**
 * Cache frontend decorator that limits the cleaning scope within a tag
 *
 * @api
 * @since 100.0.2
 */
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
     * @inheritDoc
     *
     * Enforce marking with a tag
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        $tags[] = $this->getTag();
        return parent::save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * @inheritDoc
     *
     * Limit the cleaning scope within a tag
     *
     * This matches Zend cache implementation exactly
     * (vendor/magento/framework/Cache/Frontend/Decorator/TagScope.php)
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, array $tags = [])
    {
        if ($mode == CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG) {
            // Same as Zend: Loop through tags and clean each with scope
            $result = false;
            foreach ($tags as $tag) {
                if (parent::clean(CacheConstants::CLEANING_MODE_MATCHING_TAG, [$tag, $this->getTag()])) {
                    $result = true;
                }
            }
        } else {
            if ($mode == CacheConstants::CLEANING_MODE_ALL) {
                $mode = CacheConstants::CLEANING_MODE_MATCHING_TAG;
                $tags = [$this->getTag()];
            } else {
                $tags[] = $this->getTag();
            }
            $result = parent::clean($mode, $tags);
        }
        return $result;
    }
}
