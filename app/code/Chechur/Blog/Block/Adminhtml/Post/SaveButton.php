<?php
declare(strict_types=1);

namespace Chechur\Blog\Block\Adminhtml\Post;

/**
 * Class Save Button Block
 */
class SaveButton extends \Chechur\Blog\Block\Adminhtml\Post\Edit\SaveButton
{
    /**
     * @return array|string
     */
    public function getButtonData()
    {
        if (!$this->authorization->isAllowed("Chechur_Blog::post_save")) {
            return [];
        }
        return parent::getButtonData();
    }
}
