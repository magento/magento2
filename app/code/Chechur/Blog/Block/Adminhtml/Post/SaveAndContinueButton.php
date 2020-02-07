<?php
declare(strict_types=1);

namespace Chechur\Blog\Block\Adminhtml\Post;

/**
 * Class Save And ContinueButton Block
 */
class SaveAndContinueButton extends \Chechur\Blog\Block\Adminhtml\Post\Edit\SaveAndContinueButton
{
    /**
     * @return array|string
     */
    public function getButtonData()
    {
        if (!$this->authorization->isAllowed("Chechur_Blog::post_save_and_continue")) {
            return [];
        }
        return parent::getButtonData();
    }
}
