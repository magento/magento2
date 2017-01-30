<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
