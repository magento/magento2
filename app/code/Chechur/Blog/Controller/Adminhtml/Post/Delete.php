<?php
declare(strict_types=1);

namespace Chechur\Blog\Controller\Adminhtml\Post;

use Chechur\Blog\Model\PostFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


class Delete extends Action
{

    const ADMIN_RESOURCE = 'Post';

    protected $resultPageFactory;
    protected $postFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        PostFactory $postFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->postFactory = $postFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $post = $this->postFactory->create()->load($id);

        if (!$post) {
            $this->messageManager->addError(__('Unable to process. please, try again.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/', array('_current' => true));
        }

        try {
            $post->delete();
            $this->messageManager->addSuccess(__('Your post has been deleted !'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Error while trying to delete post'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', array('_current' => true));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index', array('_current' => true));
    }
}
