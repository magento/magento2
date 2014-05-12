<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Block;

/**
 * Base widget class
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Widget extends \Magento\Backend\Block\Template
{
    /**
     * @return string
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
     */
    public function getSuffixId($suffix)
    {
        return "{$this->getId()}_{$suffix}";
    }

    /**
     * @return string
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
     */
    public function getCurrentUrl($params = array())
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
     */
    public function getButtonHtml($label, $onclick, $class = '', $buttonId = null, $dataAttr = array())
    {
        return $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            array('label' => $label, 'onclick' => $onclick, 'class' => $class, 'type' => 'button', 'id' => $buttonId)
        )->setDataAttribute(
            $dataAttr
        )->toHtml();
    }

    /**
     * @return string
     */
    public function getGlobalIcon()
    {
        return '<img src="' . $this->getViewFileUrl(
            'images/fam_link.gif'
        ) . '" alt="' . __(
            'Global Attribute'
        ) . '" title="' . __(
            'This attribute shares the same value in all stores.'
        ) . '" class="attribute-global"/>';
    }
}
