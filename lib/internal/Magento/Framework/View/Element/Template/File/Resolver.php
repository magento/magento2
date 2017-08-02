<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Template\File;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Resolver
 * @package Magento\Framework\View\Element\Template\File
 * @since 2.0.0
 */
class Resolver
{
    /**
     * Template files map
     *
     * @var []
     * @since 2.0.0
     */
    protected $_templateFilesMap = [];

    /**
     * View filesystem
     *
     * @var \Magento\Framework\View\FileSystem
     * @since 2.0.0
     */
    protected $_viewFileSystem;

    /**
     * @var Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     * @param Json $serializer
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\FileSystem $viewFileSystem,
        Json $serializer = null
    ) {
        $this->_viewFileSystem = $viewFileSystem;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Get template filename
     *
     * @param string $template
     * @param [] $params
     * @return string|bool
     * @since 2.0.0
     */
    public function getTemplateFileName($template, $params = [])
    {
        $key = $template . '_' . $this->serializer->serialize($params);
        if (!isset($this->_templateFilesMap[$key])) {
            $this->_templateFilesMap[$key] = $this->_viewFileSystem->getTemplateFileName($template, $params);
        }
        return $this->_templateFilesMap[$key];
    }
}
