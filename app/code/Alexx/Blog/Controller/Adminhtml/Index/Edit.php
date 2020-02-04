<?php

namespace Alexx\Blog\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Action;
use Alexx\Blog\Model\BlogPostsFactory;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit Admin Controller
 */
class Edit extends Action
{
    use \Alexx\Blog\Controller\Adminhtml\UseFunctions;

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
        $postId = $this->getRequest()->getParam('id');

        $model = $this->_postsFactory->create();

        if ($postId) {
            $model->load($postId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This post no longer exists.'));
                $this->_redirect('*/*/');
            }
        }

        // Restore previously entered form data from session
        $data = $this->getCurrentSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->getCurrentRegistry()->register('blognews', $model);

        /** @var Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->getClassFromObjectManager(PageFactory::class)->create();
        return $resultPage;
    }
}
