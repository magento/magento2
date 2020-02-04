<?php

namespace Alexx\Blog\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Framework\App\ObjectManager;

/**
 * Class for saving uploaded image to media directory
 * */
class PictureSaver
{
    private $_file;
    private $inputName;
    private $currentPicture = '';
    private $newPicture = false;
    private $deleteCurrentPicture = false;

    /**
     * Constructor
     *
     * @param string $inputName
     * */
    public function create($inputName)
    {
        $this->inputName = $inputName;
        return $this;
    }

    /**
     * File Uploader
     *
     * @return bool|array
     */
    public function saveFile()
    {
        /** @var Uploader $uploader */
        $uploader = ObjectManager::getInstance()->create(Uploader::class, ['fileId' => $this->inputName]);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(true);
        $uploader->setAllowCreateFolders(true);
        $result = $uploader->save($this->getMediaPath(). $this->getBlogPath());
        return $result;
    }

    /**
     * Delete file
     *
     * @param string $name
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteFile($name)
    {
        $fileName = $this->getMediaPath() . $name;
        if (ObjectManager::getInstance()->get(File::class)->isExists($fileName)) {
            ObjectManager::getInstance()->get(File::class)->deleteFile($fileName);
        }
    }

    /**
     * Delete prev picture if saving success
     */
    public function clearOnSuccess()
    {
        if ($this->deleteCurrentPicture) {
            $this->deleteFile($this->currentPicture);
        }
    }

    /**
     * Delete new picture if saving not success
     */
    public function clearOnError()
    {
        if ($this->newPicture) {
            $this->deleteFile($this->newPicture);
        }
    }

    /**
     * Upload file to media path
     *
     * @param BlogPosts $model
     * @param array $picData
     * @return array
     */
    public function getImageData()
    {
        if ($this->newPicture) {
            return $this->newPicture;
        } else {
            if ($this->deleteCurrentPicture) {
                return '';
            } else {
                return $this->currentPicture;
            }
        }
    }

    /**
     * Managing image upload
     *
     * @param string $currentPicture
     * @param array $picturePostData
     * @param array $picturePostFiles
     */
    public function uploadImage($currentPicture, $picturePostData, $picturePostFiles)
    {
        $this->currentPicture = $currentPicture;
        if (array_key_exists("delete", $picturePostData)) {
            $this->deleteCurrentPicture = true;
        }
        if ($picturePostFiles["size"] > 0) {
            $file = $this->saveFile();
            $this->newPicture = $this->getBlogPath() . ltrim($file['file'], '/');
            if ($this->currentPicture != '') {
                $this->deleteCurrentPicture = true;
            }
        }
        return $this->getImageData();
    }

    /**
     * Media path to files from picture config
     *
     * @return string
     */
    public function getBlogPath()
    {
        $pictureConfig =  ObjectManager::getInstance()->get(PictureConfig::class);
        return $pictureConfig->getBaseMediaPath() . "/";
    }

    /**
     * Media path to files
     *
     * @return string
     */
    public function getMediaPath()
    {
        return ObjectManager::getInstance()->get(Filesystem::class)
            ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }
}
