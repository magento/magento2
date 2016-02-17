<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Model;

use Zend\Feed\Writer\Feed;
use Zend\Feed\Writer\FeedFactory;

/**
 * Marker view model for indicating feed data.
 */
class FeedModel extends ViewModel
{
    /**
     * @var Feed
     */
    protected $feed;

    /**
     * @var false|string
     */
    protected $type = false;

    /**
     * A feed is always terminal
     *
     * @var bool
     */
    protected $terminate = true;

    /**
     * @return \Zend\Feed\Writer\Feed
     */
    public function getFeed()
    {
        if ($this->feed instanceof Feed) {
            return $this->feed;
        }

        if (!$this->type) {
            $options   = $this->getOptions();
            if (isset($options['feed_type'])) {
                $this->type = $options['feed_type'];
            }
        }

        $variables = $this->getVariables();
        $feed      = FeedFactory::factory($variables);
        $this->setFeed($feed);

        return $this->feed;
    }

    /**
     * Set the feed object
     *
     * @param  Feed $feed
     * @return FeedModel
     */
    public function setFeed(Feed $feed)
    {
        $this->feed = $feed;
        return $this;
    }

    /**
     * Get the feed type
     *
     * @return false|string
     */
    public function getFeedType()
    {
        if ($this->type) {
            return $this->type;
        }

        $options   = $this->getOptions();
        if (isset($options['feed_type'])) {
            $this->type = $options['feed_type'];
        }
        return $this->type;
    }
}
