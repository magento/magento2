<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block;

/**
 * Base widget class
 *
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 2.0.0
 */
class Widget extends \Magento\Backend\Block\Template
{
    /**
     * @return string
     * @since 2.0.0
     */
    public function getId()
    {
        if (null === $this->getData('id')) {
            $this->setData('id', $this->mathRandom->getUniqueHash('id_'));
        }
        return $this->getData('id');
    }

    /**
     * Get HTML ID with specified suffix
     *
     * @param string $suffix
     * @return string
     * @since 2.0.0
     */
    public function getSuffixId($suffix)
    {
        return "{$this->getId()}_{$suffix}";
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getHtmlId()
    {
        return $this->getId();
    }

    /**
     * Get current url
     *
     * @param array $params url parameters
     * @return string current url
     * @since 2.0.0
     */
    public function getCurrentUrl($params = [])
    {
        if (!isset($params['_current'])) {
            $params['_current'] = true;
        }
        return $this->getUrl('*/*/*', $params);
    }

    /**
     * @param string $label
     * @param string|null $title
     * @param string|null $link
     * @return void
     * @since 2.0.0
     */
    protected function _addBreadcrumb($label, $title = null, $link = null)
    {
        $this->getLayout()->getBlock('breadcrumbs')->addLink($label, $title, $link);
    }

    /**
     * Create button and return its html
     *
     * @param string $label
     * @param string $onclick
     * @param string $class
     * @param string $buttonId
     * @param array $dataAttr
     * @return string
     * @since 2.0.0
     */
    public function getButtonHtml($label, $onclick, $class = '', $buttonId = null, $dataAttr = [])
    {
        return $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            ['label' => $label, 'onclick' => $onclick, 'class' => $class, 'type' => 'button', 'id' => $buttonId]
        )->setDataAttribute(
            $dataAttr
        )->toHtml();
    }
}
