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
        // NOT_MATCHING_TAG has no safe implementation here: adding the scope tag to the exclusion
        // list makes it match nothing (every entry in scope carries the scope tag), while forwarding
        // the caller's tags unmodified drops scope enforcement entirely and can delete entries
        // belonging to other cache types sharing this backend. Refuse it, matching the legacy Zend
        // adapter's own prohibition of this mode (see Frontend\Adapter\Zend::clean()). Callers that
        // need the raw, unscoped capability (e.g. low-level adapter tests) should use
        // getLowLevelFrontend()->clean() instead, which bypasses this decorator entirely.
        if ($mode == CacheConstants::CLEANING_MODE_NOT_MATCHING_TAG) {
            throw new \InvalidArgumentException(
                "Tag-scoped cache frontend does not support the cleaning mode '{$mode}'."
            );
        }

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
