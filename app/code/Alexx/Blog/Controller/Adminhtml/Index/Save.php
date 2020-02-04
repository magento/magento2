<?php

namespace Alexx\Blog\Controller\Adminhtml\Index;

use Alexx\Blog\Model\BlogPostsFactory;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\Action\Action;
use Alexx\Blog\Model\BlogPostSaver;

/**
 * Class Save Admin Controller
 */
class Save extends Action
{
    use \Alexx\Blog\Controller\Adminhtml\UseFunctions;

    private $_postsFactory;

    /**
     * Constructor
     *
     * @param ActionContext $context
     * @param BlogPostsFactory $postsFactory
     * */
    public function __construct(
        ActionContext $context,
        BlogPostsFactory $postsFactory
    ) {
        parent::__construct($context);

        $this->_postsFactory = $postsFactory;
    }

    /**
     * Redirect with error message
     *
     * @param string $message
     * @param string $path
     * @param array $arguments
     */
    public function redirectError($message, $path, $arguments = [])
    {
        $this->messageManager->addError($message);
        $this->_redirect($path, $arguments);
    }

    /**
     * Redirect with success message
     *
     * @param string $result
     */
    public function redirectSuccess($result)
    {
        $this->messageManager->addSuccess(__('The post has been saved.'));

        // Check if 'Save and Continue'
        if ($this->getRequest()->getParam('back')) {
            $this->_redirect('*/*/edit', ['id' => $result, '_current' => true]);
            return;
        }
        // Go to grid page
        $this->_redirect('*/*/');
    }

    /**
     * Main logic method
     */
    public function execute()
    {
        if ($this->getRequest()->getPost()) {
            $postModel = $this->getClassFromObjectManager(BlogPostSaver::class)->create($this, $this->_postsFactory);

            if (!$postModel->loadFormData('blog_data')) {
                $this->redirectError(__('This post no longer exists.'), '*/*/');
                return;
            }

            try {
                $postModel->loadPictureData('blog_picture');
            } catch (\Exception $e) {
                $this->redirectError($e->getMessage(), '*/*/edit', ['id' => $postModel->getFormData('entity_id')]);
                return;
            }

            try {
                $modelId = $postModel->save();
                if ($modelId) {
                    $this->redirectSuccess($modelId);
                    return;
                }
            } catch (\Exception $e) {
                $this->redirectError($e->getMessage(), '*/*/edit', ['id' => $postModel->getFormData('entity_id')]);
                return;
            }

            $this->getCurrentSession()->setFormData($postModel->getFormData());
            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getPost()['entity_id']]);
        }
    }
}
