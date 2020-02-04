<?php

namespace Alexx\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ObjectManager;

/**
 * Simple Model BlogPosts
 */
class BlogPosts extends AbstractModel
{
    const TBL_NAME = 'alexx_blog_posts';
    const TBL_ENTITY = 'entity_id';

    /**
     * Generates url to image
     */
    public function getImageUrl()
    {
        $picureConfig = ObjectManager::getInstance()->get(PictureConfig::class);
        return $picureConfig->getBlogImageUrl($this->getPicture());
    }

    /**
     * Getting 5 last posts
     */
    public function getLatestPosts()
    {
        return $this->getCollection()->addOrder('main_table.created_at', 'desc')->setPageSize(5);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\BlogPosts::class);
    }
}
