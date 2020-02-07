<?php
declare(strict_types=1);

namespace Chechur\Blog\Model;


use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Post extends AbstractModel implements IdentityInterface
{
    /** @var string */
    const CACHE_TAG = 'chechur_blog_post';

    /** @var string */
    protected $_cacheTag = 'chechur_blog_post';

    /** @var string */
    protected $_eventPrefix = 'chechur_blog_post';


    /**
     *  init resourse model
     */
    protected function _construct()
    {
        $this->_init('Chechur\Blog\Model\ResourceModel\Post');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

}
