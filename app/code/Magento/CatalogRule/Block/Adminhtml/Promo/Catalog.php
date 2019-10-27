<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

/**
 * Catalog price rules
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogRule\Block\Adminhtml\Promo;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\ObjectManager;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as CatalogRuleCollectionFactory;

/**
 * Catalog Rule block
 *
 * @api
 * @since 100.0.2
 */
class Catalog extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var CatalogRuleCollectionFactory
     */
    private $catalogRuleCollectionFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param array $data
     * @param CatalogRuleCollectionFactory|null $catalogRuleCollectionFactory
     */
    public function __construct(
        Context $context,
        array $data = [],
        CatalogRuleCollectionFactory $catalogRuleCollectionFactory = null
    ) {
        $this->catalogRuleCollectionFactory = $catalogRuleCollectionFactory ?: ObjectManager::getInstance()->get(
            CatalogRuleCollectionFactory::class
        );
        parent::__construct($context, $data);
    }

    /**
     * Sets block template and necessary data
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_CatalogRule';
        $this->_controller = 'adminhtml_promo_catalog';
        $this->_headerText = __('Catalog Price Rule');
        $this->_addButtonLabel = __('Add New Rule');
        parent::_construct();
        if ($this->catalogRuleCollectionFactory->create()->getSize()) {
            $this->buttonList->add(
                'apply_rules',
                [
                    'label' => __('Apply Rules'),
                    'onclick' => "location.href='" . $this->getUrl('catalog_rule/*/applyRules') . "'",
                    'class' => 'apply'
                ]
            );
        }
    }
}
