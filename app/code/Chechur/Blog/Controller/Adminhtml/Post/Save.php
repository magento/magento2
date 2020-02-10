<?php
declare(strict_types=1);

namespace Chechur\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action;

class Save extends Action
{
    /**
     * Constanta
     */
    const ADMIN_RESOURCE = 'Post';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Save constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param PostFactory $postFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Chechur\Blog\Model\PostFactory $postFactory,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    )
    {
        $this->dataPersistor = $dataPersistor;
        $this->resultPageFactory = $resultPageFactory;
        $this->postFactory = $postFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue('contact');

        if ($data) {

            if (empty($data['post_id'])) {
                $data['post_id'] = null;
            }
            $id = $data['post_id'];
            $post = $this->postFactory->create()->load($id);

            $data = $this->_filterFoodData($data);
            $data = array_filter($data, function ($value) {
                return $value !== '';
            });
            $post->setData($data);

            try {
                $post->save();
                $this->messageManager->addSuccessMessage(__('You saved the Post.'));
                $this->dataPersistor->clear('chechur_blog_post');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $post->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager
                    ->addExceptionMessage($e, __('Something went wrong while saving the Post.'));
            }

            $this->dataPersistor->set('chechur_blog_post', $data);
            return $resultRedirect->setPath('*/*/edit',
                [
                    'post_id' => $this->getRequest()->getParam('post_id')
                ]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param array $rawData
     * @return array
     */
    public function _filterFoodData(array $rawData)
    {
        //Replace image with fileuploader field name
        $data = $rawData;
        if (isset($data['image'][0]['name'])) {
            $data['image'] = $data['image'][0]['name'];
        } else {
            $data['image'] = null;
        }
        return $data;
    }
}
