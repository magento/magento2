<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Backend;

use Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface;
use Magento\Config\Model\Config\Backend\Image\Logo as ConfigLogo;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Ui\Model\FileUploader\Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Logo extends ConfigLogo
{
    /**
     * @var Config
     */
    protected $imageConfig;

    /**
     * @inheritDoc
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        UploaderFactory $uploaderFactory,
        RequestDataInterface $requestData,
        Filesystem $filesystem,
        Config $imageConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $uploaderFactory,
            $requestData,
            $filesystem,
            $resource,
            $resourceCollection,
            $data
        );
        $this->imageConfig = $imageConfig;
    }

    /**
     * Save uploaded file before saving config value
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $values = $this->getValue();
        $value = reset($values) ?: [];
        if (!isset($value['file'])) {
            throw new LocalizedException(
                __($this->getData('field_config/field') . ' does not contain field \'file\'')
            );
        }
        if (isset($value['exists'])) {
            $this->setValue($value['file']);
            return $this;
        }

        $filename = $value['file'];
        try {
             $result = $this->_mediaDirectory->copyFile(
                $this->imageConfig->getTmpMediaPath($filename),
                $this->_appendScopeInfo(self::UPLOAD_DIR) . '/' . $filename
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        if ($result) {
            $this->_mediaDirectory->delete($this->imageConfig->getTmpMediaPath($filename));
            if ($this->_addWhetherScopeInfo()) {
                $filename = $this->_prependScopeInfo($filename);
            }
            $this->setValue($filename);
        } else {
            $this->unsValue();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function afterLoad()
    {
        $value = $this->getValue();
        if ($value) {
            $fileName = '/' . self::UPLOAD_DIR . '/' . $value;
            $stat = $this->_mediaDirectory->stat($fileName);
            $this->setValue([
                [
                    'url' => $this->imageConfig->getStoreMediaUrl() .  $fileName,
                    'file' => $value,
                    'size' => is_array($stat) ? $stat['size'] : 0,
                    'exists' => true
                ]
            ]);
        }
        return $this;
    }
}
