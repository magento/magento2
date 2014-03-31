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
 * @category    Magento
 * @package     Magento_SalesRule
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\SalesRule\Model\Rule\Action;

class Collection extends \Magento\Rule\Model\Action\Collection
{
    /**
     * @param \Magento\View\Url $viewUrl
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Rule\Model\ActionFactory $actionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\View\Url $viewUrl,
        \Magento\View\LayoutInterface $layout,
        \Magento\Rule\Model\ActionFactory $actionFactory,
        array $data = array()
    ) {
        parent::__construct($viewUrl, $layout, $actionFactory, $data);
        $this->setType('Magento\SalesRule\Model\Rule\Action\Collection');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $actions = parent::getNewChildSelectOptions();
        $actions = array_merge_recursive(
            $actions,
            array(array('value' => 'Magento\SalesRule\Model\Rule\Action\Product', 'label' => __('Update the Product')))
        );
        return $actions;
    }
}
