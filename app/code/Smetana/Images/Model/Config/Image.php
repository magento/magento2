<?php
namespace Smetana\Images\Model\Config;

/**
 * Image Operations
 */
class Image extends \Magento\Config\Model\Config\Backend\Image
{
    /**
     * The tail part of directory path for uploading
     *
     * @var string
     */
    const UPLOAD_DIR = 'products_image';

    /**
     * Returning path to directory for upload file
     *
     * @return string
     * @throw \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUploadDir(): string
    {
        return $this->_mediaDirectory->getAbsolutePath($this->_appendScopeInfo(self::UPLOAD_DIR));
    }

    /**
     * Making a decision about whether to add info about the scope
     *
     * @return boolean
     */
    protected function _addWhetherScopeInfo(): bool
    {
        return true;
    }

    /**
     * Getting for allowed extensions of uploaded files
     *
     * @return array
     */
    protected function _getAllowedExtensions(): array
    {
        return ['jpg', 'jpeg'];
    }

    /**
     * Changing process of saving image
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
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