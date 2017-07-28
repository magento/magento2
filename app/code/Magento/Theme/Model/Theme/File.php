<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\View\Design\Theme\Customization\FileInterface as CustomizationFileInterface;
use Magento\Framework\View\Design\Theme\FileInterface;

/**
 * Theme files model class
 * @since 2.0.0
 */
class File extends AbstractModel implements FileInterface
{
    /**
     * {@inheritdoc}
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'theme_file';

    /**
     * {@inheritdoc}
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'file';

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    protected $_theme;

    /**
     * @var \Magento\Framework\View\Design\Theme\Customization\FileServiceFactory
     * @since 2.0.0
     */
    protected $_fileServiceFactory;

    /**
     * @var CustomizationFileInterface
     * @since 2.0.0
     */
    protected $_fileService;

    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory
     * @since 2.0.0
     */
    protected $_themeFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory
     * @param \Magento\Framework\View\Design\Theme\Customization\FileServiceFactory $fileServiceFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory,
        \Magento\Framework\View\Design\Theme\Customization\FileServiceFactory $fileServiceFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_themeFactory = $themeFactory;
        $this->_fileServiceFactory = $fileServiceFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Theme files model initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Theme\Model\ResourceModel\Theme\File::class);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     * @since 2.0.0
     */
    public function setCustomizationService(CustomizationFileInterface $fileService)
    {
        $this->_fileService = $fileService;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return CustomizationFileInterface
     * @throws \UnexpectedValueException
     * @since 2.0.0
     */
    public function getCustomizationService()
    {
        if (!$this->_fileService && $this->hasData('file_type')) {
            $this->_fileService = $this->_fileServiceFactory->create($this->getData('file_type'));
        } elseif (!$this->_fileService) {
            throw new \UnexpectedValueException('Type of file is empty');
        }
        return $this->_fileService;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setTheme(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $this->_theme = $theme;
        $this->setData('theme_id', $theme->getId());
        $this->setData('theme_path', $theme->getThemePath());
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getTheme()
    {
        $theme = $this->_themeFactory->create($this->getData('theme_id'));
        if (!$theme) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Theme id should be set'));
        }
        return $theme;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setFileName($fileName)
    {
        $this->setData('file_name', $fileName);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getFileName()
    {
        return $this->getData('file_name') ?: basename($this->getData('file_path'));
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getFullPath()
    {
        return $this->getCustomizationService()->getFullPath($this);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getContent()
    {
        return $this->getData('content');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getFileInfo()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getFileName(),
            'temporary' => $this->getData('is_temporary') ? $this->getId() : 0
        ];
    }

    /**
     * Prepare file before it will be saved
     *
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave()
    {
        $fileService = $this->getCustomizationService();
        $fileService->prepareFile($this);
        $fileService->save($this);
        return parent::beforeSave();
    }

    /**
     * Prepare file before it will be deleted
     *
     * @return $this
     * @since 2.0.0
     */
    public function beforeDelete()
    {
        $fileService = $this->getCustomizationService();
        $fileService->delete($this);
        return parent::beforeDelete();
    }
}
