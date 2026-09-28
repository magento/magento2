<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\UiComponent\ContentType;

use Magento\Framework\View\FileSystem;
use Magento\Framework\View\TemplateEnginePool;

/**
 * Class AbstractContentType
 */
abstract class AbstractContentType implements ContentTypeInterface
{
    /**
     * @var FileSystem
     */
    protected $filesystem;

    /**
     * @var TemplateEnginePool
     */
    protected $templateEnginePool;

    /**
     * Constructor
     *
     * @param FileSystem $filesystem
     * @param TemplateEnginePool $templateEnginePool
     */
    public function __construct(
        FileSystem $filesystem,
        TemplateEnginePool $templateEnginePool
    ) {
        $this->filesystem = $filesystem;
        $this->templateEnginePool = $templateEnginePool;
    }
}
