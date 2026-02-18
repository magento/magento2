<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Design\Theme\Customization;

/**
 * Theme customization files factory
 */
class FileServiceFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var array
     */
    private $_types;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ConfigInterface $config
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, ConfigInterface $config)
    {
        $this->_objectManager = $objectManager;
        $this->_types = $config->getFileTypes();
    }

    /**
     * Create new instance
     *
     * @param string $type
     * @param array $data
     * @return \Magento\Framework\View\Design\Theme\Customization\FileInterface
     * @throws \InvalidArgumentException
     */
    public function create($type, array $data = [])
    {
        if (empty($this->_types[$type])) {
            throw new \InvalidArgumentException('Unsupported file type');
        }
        $fileService = $this->_objectManager->get($this->_types[$type], [$data]);
        if (!$fileService instanceof \Magento\Framework\View\Design\Theme\Customization\FileInterface) {
            throw new \InvalidArgumentException('Service don\'t implement interface');
        }
        return $fileService;
    }
}
