<?php
declare(strict_types=1);

namespace Chechur\Blog\Block\Adminhtml\Post;

/**
 * Class Delete Button Block
 */
class DeleteButton extends \Chechur\Blog\Block\Adminhtml\Post\Edit\DeleteButton
{

    /**
     * Get button data
     * @return array|string
     */
    public function getButtonData()
    {

        if (!$this->authorization->isAllowed("Chechur_Blog::post_delete")) {
            return [];
        }

        return parent::getButtonData();
    }
}
