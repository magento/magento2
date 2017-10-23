<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Page\Config\Generator;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config\Structure;

class Body implements Layout\GeneratorInterface
{
    /**
     * Type of generator
     */
    const TYPE = 'body';

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Page\Config $pageConfig
     */
    public function __construct(\Magento\Framework\View\Page\Config $pageConfig)
    {
        $this->pageConfig = $pageConfig;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     *
     * @param Layout\Reader\Context $readerContext
     * @param Layout\Generator\Context $generatorContext
     * @return $this
     */
    public function process(Layout\Reader\Context $readerContext, Layout\Generator\Context $generatorContext)
    {
        $structure = $readerContext->getPageConfigStructure();
        $this->processBodyClasses($structure);
        return $this;
    }

    /**
     * Process body classes, add to page configuration from scheduled structure
     *
     * @param \Magento\Framework\View\Page\Config\Structure $pageStructure
     * @return $this
     */
    protected function processBodyClasses(Structure $pageStructure)
    {
        foreach ($pageStructure->getBodyClasses() as $class) {
            $this->pageConfig->addBodyClass($class);
        }
        return $this;
    }
}
