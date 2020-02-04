<?php

namespace Alexx\Blog\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\AbstractModel;
use Magento\MediaStorage\Model\File\Uploader;

/**
 * Class for create/edit BlogPost data row
 * */
class BlogPostSaver
{
    /**@var AbstractModel $model */
    private $model;
    private $pictureSaver = null;
    private $currentAction;
    private $formData;

    /**
     * Loads form data from form to model
     *
     * @param string $postDataField
     **/
    public function loadFormData($postDataField)
    {
        $this->formData = $this->currentAction->getRequest()->getParam($postDataField);
        $postId = array_key_exists($this->model::TBL_ENTITY, $this->formData) ?
            $this->formData[$this->model::TBL_ENTITY] :
            null;
        if ($postId) {
            $this->model->load($postId);
            if (empty($this->model->getData())) {
                return false;
            }
        }
        return true;
    }

    /**
     * Uploads  image posted by form
     *
     * @param string $pictureDataField
     **/
    public function loadPictureData($pictureDataField)
    {
        $currentPicture = $this->model->getData('picture');
        $picturePostData = $this->currentAction->getRequest()->getParam($pictureDataField);
        $picturePostFiles = $this->currentAction->getRequest()->getFiles($pictureDataField);
        if (!$picturePostData) {
            $picturePostData = [];
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->pictureSaver = $objectManager->get(PictureSaver::class)->create($pictureDataField);
        $this->formData['picture'] =
            $this->pictureSaver->uploadImage($currentPicture, $picturePostData, $picturePostFiles);
    }

    /**
     * Init functin
     *
     * @param \Magento\Framework\App\Action\Action $currentAction
     * @param \Magento\Framework\Model\AbstractModel $modelFactory
     **/
    public function create($currentAction, $modelFactory)
    {
        $this->currentAction = $currentAction;
        $this->model = $modelFactory->create();
        return $this;
    }

    /**
     * Gets data from currently posted form
     *
     * @param string $field
     **/
    public function getFormData($field = null)
    {
        if ($field) {
            if (array_key_exists($field, $this->formData)) {
                return $this->formData[$field];
            } else {
                return null;
            }

        }
        return $this->formData;
    }

    /**
     * Saves model data to db
     **/
    public function save()
    {
        $this->model->setData($this->formData);
        try {
            // Save news
            $this->model->save();
            // Display success message
            if ($this->pictureSaver) {
                $this->pictureSaver->clearOnSuccess();
            }
            return $this->model->getId();
        } catch (\Exception $e) {
            if ($this->pictureSaver) {
                $this->pictureSaver->clearOnError();
            }
            throw $e;
        }
        return false;
    }
}
