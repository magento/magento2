<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category\Attribute\Source;

use Magento\Theme\Model\PageLayout\Config\Builder;

/**
 * Catalog category landing page attribute source
 */
class Layout extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;

    /**
     * @inheritdoc
     * @var array
     * @deprecated 103.0.1 since the cache is now handled by Builder::$configFiles
     */
    protected $_options = null;

    /**
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     */
    public function __construct(\Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder)
    {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions()
    {
        $options = $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray();
        array_unshift($options, ['value' => '', 'label' => __('No layout updates')]);
        $this->_options = $options;

        return $options;
    }
}
