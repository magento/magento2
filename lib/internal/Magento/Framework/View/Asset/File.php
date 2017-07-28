<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

/**
 * A locally available static view file asset that can be referred with a file path
 *
 * This class is a value object with lazy loading of some of its data (content, physical file path)
 *
 * @api
 * @since 2.0.0
 */
class File implements MergeableInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $filePath;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $module;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $contentType;

    /**
     * @var ContextInterface
     * @since 2.0.0
     */
    protected $context;

    /**
     * @var Source
     * @since 2.0.0
     */
    protected $source;

    /**
     * @var string|bool
     * @since 2.0.0
     */
    private $resolvedFile;

    /**
     * @var Minification
     * @since 2.0.0
     */
    private $minification;

    /**
     * @var string
     * @since 2.2.0
     */
    private $sourceContentType;

    /**
     * @param Source $source
     * @param ContextInterface $context
     * @param string $filePath
     * @param string $module
     * @param string $contentType
     * @param Minification $minification
     * @since 2.0.0
     */
    public function __construct(
        Source $source,
        ContextInterface $context,
        $filePath,
        $module,
        $contentType,
        Minification $minification
    ) {
        $this->source = $source;
        $this->context = $context;
        $this->filePath = $filePath;
        $this->module = $module;
        $this->contentType = $contentType;
        $this->minification = $minification;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUrl()
    {
        return $this->context->getBaseUrl() . $this->getPath();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSourceUrl()
    {
        return $this->context->getBaseUrl() . $this->getRelativeSourceFilePath();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPath()
    {
        $result = '';
        $result = $this->join($result, $this->context->getPath());
        $result = $this->join($result, $this->module);
        $result = $this->join($result, $this->filePath);
        $result = $this->minification->addMinifiedSign($result);
        return $result;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getRelativeSourceFilePath()
    {
        $path = $this->filePath;
        $sourcePath = $this->source->findSource($this);
        if ($sourcePath) {
            $origExt = pathinfo($path, PATHINFO_EXTENSION);
            $ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
            $path = str_replace('.' . $origExt, '.' . $ext, $this->filePath);
        }
        $result = '';
        $result = $this->join($result, $this->context->getPath());
        $result = $this->join($result, $this->module);
        $result = $this->join($result, $path);
        return $result;
    }

    /**
     * Subroutine for building path
     *
     * @param string $path
     * @param string $item
     * @return string
     * @since 2.0.0
     */
    private function join($path, $item)
    {
        return trim($path . ($item ? '/' . $item : ''), '/');
    }

    /**
     * {@inheritdoc}
     * @throws File\NotFoundException if file cannot be resolved
     * @since 2.0.0
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
     * Get source content type
     *
     * @return string
     * @since 2.2.0
     */
    public function getSourceContentType()
    {
        if ($this->sourceContentType === null) {
            $this->sourceContentType = $this->source->getSourceContentType($this);
        }
        return $this->sourceContentType;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getContent()
    {
        $content = $this->source->getContent($this);
        if (false === $content) {
            throw new File\NotFoundException("Unable to get content for '{$this->getPath()}'");
        }
        return $content;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * {@inheritdoc}
     * @return File\Context
     * @since 2.0.0
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getModule()
    {
        return $this->module;
    }
}
