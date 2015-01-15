<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

/**
 * A locally available static view file asset that can be referred with a file path
 *
 * This class is a value object with lazy loading of some of its data (content, physical file path)
 */
class File implements MergeableInterface
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $module;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var string|bool
     */
    private $resolvedFile;

    /**
     * @param Source $source
     * @param ContextInterface $context
     * @param string $filePath
     * @param string $module
     * @param string $contentType
     */
    public function __construct(Source $source, ContextInterface $context, $filePath, $module, $contentType)
    {
        $this->source = $source;
        $this->context = $context;
        $this->filePath = $filePath;
        $this->module = $module;
        $this->contentType = $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->context->getBaseUrl() . $this->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        $result = '';
        $result = $this->join($result, $this->context->getPath());
        $result = $this->join($result, $this->module);
        $result = $this->join($result, $this->filePath);
        return $result;
    }

    /**
     * Subroutine for building path
     *
     * @param string $path
     * @param string $item
     * @return string
     */
    private function join($path, $item)
    {
        return trim($path . ($item ? '/' . $item : ''), '/');
    }

    /**
     * {@inheritdoc}
     * @throws File\NotFoundException if file cannot be resolved
     */
    public function getSourceFile()
    {
        if (null === $this->resolvedFile) {
            $this->resolvedFile = $this->source->getFile($this);
            if (false === $this->resolvedFile) {
                throw new File\NotFoundException("Unable to resolve the source file for '{$this->getPath()}'");
            }
        }
        return $this->resolvedFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return (string)$this->source->getContent($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * {@inheritdoc}
     * @return File\Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function getModule()
    {
        return $this->module;
    }
}
