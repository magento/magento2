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
namespace Magento\Cms\Block\Adminhtml\Page\Grid\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var Action\UrlBuilder
     */
    protected $actionUrlBuilder;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param Action\UrlBuilder $actionUrlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        Action\UrlBuilder $actionUrlBuilder,
        array $data = array()
    ) {
        $this->actionUrlBuilder = $actionUrlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * Render action
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
    {
        $href = $this->actionUrlBuilder->getUrl(
            $row->getIdentifier(),
            $row->getData('_first_store_id'),
            $row->getStoreCode()
        );
        return '<a href="' . $href . '" target="_blank">' . __('Preview') . '</a>';
    }
}
