<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Theme;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\View\Design\Theme\FileInterface;
use Magento\Framework\View\Design\Theme\Customization\FileInterface as CustomizationFileInterface;

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
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    protected $_theme;

    /**
     * @var \Magento\Framework\View\Design\Theme\Customization\FileServiceFactory
     */
    protected $_fileServiceFactory;

    /**
     * @var CustomizationFileInterface
     */
    protected $_fileService;

    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory
     */
    protected $_themeFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory
     * @param \Magento\Framework\View\Design\Theme\Customization\FileServiceFactory $fileServiceFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory,
        \Magento\Framework\View\Design\Theme\Customization\FileServiceFactory $fileServiceFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
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
        $this->_init('Magento\Core\Model\Resource\Theme\File');
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
     * @throws \UnexpectedValueException
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
     * @throws \Magento\Framework\Exception
     */
    public function getTheme()
    {
        $theme = $this->_themeFactory->create($this->getData('theme_id'));
        if (!$theme) {
            throw new \Magento\Framework\Exception('Theme id should be set');
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
        return array(
            'id' => $this->getId(),
            'name' => $this->getFileName(),
            'temporary' => $this->getData('is_temporary') ? $this->getId() : 0
        );
    }

    /**
     * Prepare file before it will be saved
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        $fileService = $this->getCustomizationService();
        $fileService->prepareFile($this);
        $fileService->save($this);
        return parent::_beforeSave();
    }

    /**
     * Prepare file before it will be deleted
     *
     * @return $this
     */
    protected function _beforeDelete()
    {
        $fileService = $this->getCustomizationService();
        $fileService->delete($this);
        return parent::_beforeDelete();
    }
}
