<?php

namespace Alexx\Blog\Controller\Adminhtml\Index;

use Alexx\Blog\Model\BlogPostsFactory;
use Alexx\Blog\Model\PictureSaver;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\ObjectManager;

/**
 * Class Delete Admin Controller
 */
class Delete extends Action
{
    private $_postsFactory;

    /**
     * Constructor
     *
     * @param ActionContext $context
     * @param BlogPostsFactory $postsFactory
     */
    public function __construct(
        ActionContext $context,
        BlogPostsFactory $postsFactory
    ) {
        parent::__construct($context);
        $this->_postsFactory = $postsFactory;
    }

    /**
     * Main logic method
     */
    public function execute()
    {
        if ($this->getRequest()->getPost()) {
            $postId = $this->getRequest()->getParam('id');
            $model = $this->_postsFactory->create();
            if ($postId) {
                $blogPost = $model->load($postId);

                if (!empty($blogPost->getData())) {

                    if ($blogPost->getPicture() != '' && $blogPost->getPicture() !== null) {
                        ObjectManager::getInstance()->get(PictureSaver::class)->deleteFile($blogPost->getPicture());
                    }
                    $blogPost->delete();
                    $this->messageManager->addSuccess(__('The post has been deleted.'));
                } else {
                    $this->messageManager->addError(__('This post no longer exists.'));
                }
            } else {
                $this->messageManager->addError(__('Wrong request. Try again'));
            }
        } else {
            $this->messageManager->addError(__('Wrong request. Try again'));
        }

        $this->_redirect('*/*/');
    }
}
