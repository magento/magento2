<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\ContentType;

use Magento\Framework\View\FileSystem;
use Magento\Framework\View\TemplateEnginePool;

/**
 * Class AbstractContentType
 * @since 2.0.0
 */
abstract class AbstractContentType implements ContentTypeInterface
{
    /**
     * @var FileSystem
     * @since 2.0.0
     */
    protected $filesystem;

    /**
     * @var TemplateEnginePool
     * @since 2.0.0
     */
    protected $templateEnginePool;

    /**
     * Constructor
     *
     * @param FileSystem $filesystem
     * @param TemplateEnginePool $templateEnginePool
     * @since 2.0.0
     */
    public function __construct(
        FileSystem $filesystem,
        TemplateEnginePool $templateEnginePool
    ) {
        $this->filesystem = $filesystem;
        $this->templateEnginePool = $templateEnginePool;
    }
}
