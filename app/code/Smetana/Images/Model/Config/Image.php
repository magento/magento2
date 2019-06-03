<?php
namespace Smetana\Images\Model\Config;

class Image extends \Magento\Config\Model\Config\Backend\Image
{
    /**
     * The tail part of directory path for uploading
     *
     */
    const UPLOAD_DIR = 'products_image';

    /**
     * Return path to directory for upload file
     *
     * @return string
     * @throw \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUploadDir()
    {
        return $this->_mediaDirectory->getAbsolutePath($this->_appendScopeInfo(self::UPLOAD_DIR));
    }

    /**
     * Makes a decision about whether to add info about the scope.
     *
     * @return boolean
     */
    protected function _addWhetherScopeInfo()
    {
        return true;
    }

    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg'];
    }

    public function beforeSave()
    {
        if (!empty($this->getFileData())) {
            $files = @scandir($this->_getUploadDir());
            if ($files) {
                foreach ($files as $file) {
                    @unlink($this->_getUploadDir() . '/' . $file);
                }
                if (mime_content_type($this->getFileData()['tmp_name']) != 'image/jpeg') {
                    throw new \Magento\Framework\Exception\LocalizedException(__('%1', 'The file has the wrong extension'));
                }
            }
        }

        return parent::beforeSave();
    }
}