<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category\Attribute\Source;

/**
 * Catalog category landing page attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Layout extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Core\Model\PageLayout\Config\Builder
     */
    protected $pageLayoutBuilder;

    /**
     * @param \Magento\Core\Model\PageLayout\Config\Builder $pageLayoutBuilder
     */
    public function __construct(\Magento\Core\Model\PageLayout\Config\Builder $pageLayoutBuilder)
    {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray();
            array_unshift($this->_options, ['value' => '', 'label' => __('No layout updates')]);
        }
        return $this->_options;
    }
}
