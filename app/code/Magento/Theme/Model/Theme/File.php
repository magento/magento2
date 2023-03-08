<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\View\Design\Theme\Customization\FileInterface as CustomizationFileInterface;
use Magento\Framework\View\Design\Theme\Customization\FileServiceFactory;
use Magento\Framework\View\Design\Theme\FileInterface;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\ResourceModel\Theme\File as ResourceThemeFile;
use UnexpectedValueException;

/**
 * Theme files model class
 */
class File extends AbstractModel implements FileInterface
{
    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $_eventPrefix = 'theme_file';

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $_eventObject = 'file';

    /**
     * @var ThemeInterface
     */
    protected $_theme;

    /**
     * @var FileServiceFactory
     */
    protected $_fileServiceFactory;

    /**
     * @var CustomizationFileInterface
     */
    protected $_fileService;

    /**
     * @var FlyweightFactory
     */
    protected $_themeFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FlyweightFactory $themeFactory
     * @param FileServiceFactory $fileServiceFactory
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FlyweightFactory $themeFactory,
        FileServiceFactory $fileServiceFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
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
     */
    protected function _construct()
    {
        $this->_init(ResourceThemeFile::class);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
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
     * @throws UnexpectedValueException
     */
    public function getCustomizationService()
    {
        if (!$this->_fileService && $this->hasData('file_type')) {
            $this->_fileService = $this->_fileServiceFactory->create($this->getData('file_type'));
        } elseif (!$this->_fileService) {
            throw new UnexpectedValueException('Type of file is empty');
        }
        return $this->_fileService;
    }

    /**
     * {@inheritdoc}
     */
    public function setTheme(ThemeInterface $theme)
    {
        $this->_theme = $theme;
        $this->setData('theme_id', $theme->getId());
        $this->setData('theme_path', $theme->getThemePath());
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws LocalizedException
     */
    public function getTheme()
    {
        $theme = $this->_themeFactory->create($this->getData('theme_id'));
        if (!$theme) {
            throw new LocalizedException(__('Theme id should be set'));
        }
        return $theme;
    }

    /**
     * {@inheritdoc}
     */
    public function setFileName($fileName)
    {
        $this->setData('file_name', $fileName);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileName()
    {
        return $this->getData('file_name') ?: basename($this->getData('file_path'));
    }

    /**
     * {@inheritdoc}
     */
    public function getFullPath()
    {
        return $this->getCustomizationService()->getFullPath($this);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->getData('content');
    }

    /**
     * {@inheritdoc}
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
     */
    public function beforeDelete()
    {
        $fileService = $this->getCustomizationService();
        $fileService->delete($this);
        return parent::beforeDelete();
    }
}
