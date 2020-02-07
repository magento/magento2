<?php
declare(strict_types=1);

namespace Chechur\Blog\Controller\Adminhtml\Post;


use Chechur\Blog\Model\PostFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;

class Save extends Action
{
    /**
     *
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
        Context $context,
        PageFactory $resultPageFactory,
        PostFactory $postFactory,
        RedirectFactory $resultRedirectFactory,
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
                $this->messageManager->addSuccess(__('Successfully saved the item.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', ['id' => $post->getId()]);
            }
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
