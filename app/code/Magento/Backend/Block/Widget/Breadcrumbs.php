<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget;

/**
 * Magento_Backend page breadcrumbs
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Breadcrumbs extends \Magento\Backend\Block\Template
{
    /**
     * Breadcrumbs links
     *
     * @var array
     * @since 2.0.0
     */
    protected $_links = [];

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Backend::widget/breadcrumbs.phtml';

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->addLink(__('Home'), __('Home'), $this->getUrl('*'));
    }

    /**
     * @param string $label
     * @param string|null $title
     * @param string|null $url
     * @return $this
     * @since 2.0.0
     */
    public function addLink($label, $title = null, $url = null)
    {
        if (empty($title)) {
            $title = $label;
        }
        $this->_links[] = ['label' => $label, 'title' => $title, 'url' => $url];
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        // TODO - Moved to Beta 2, no breadcrumbs displaying in Beta 1
        // $this->assign('links', $this->_links);
        return parent::_beforeToHtml();
    }
}
