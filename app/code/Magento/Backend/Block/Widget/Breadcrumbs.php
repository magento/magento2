<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget;

/**
 * Magento_Backend page breadcrumbs
 *
 * @api
 * @since 100.0.2
 */
class Breadcrumbs extends \Magento\Backend\Block\Template
{
    /**
     * Breadcrumbs links
     *
     * @var array
     */
    protected $_links = [];

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/breadcrumbs.phtml';

    /**
     * Add homepage to breadcrumbs
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addLink(__('Home'), __('Home'), $this->getUrl('*'));
    }

    /**
     * Add a link to the breadcrumbs
     *
     * @param string $label
     * @param string|null $title
     * @param string|null $url
     * @return $this
     */
    public function addLink($label, $title = null, $url = null)
    {
        if (empty($title)) {
            $title = $label;
        }
        $this->_links[] = ['label' => $label, 'title' => $title, 'url' => $url];
        return $this;
    }
}
