<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Cache;

/**
 * Cache management form page
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     * @since 2.0.0
     */
    protected $cacheTypeList;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        array $data = []
    ) {
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Initialize cache management form
     *
     * @return $this
     * @since 2.0.0
     */
    public function initForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('cache_enable', ['legend' => __('Cache Control')]);

        $fieldset->addField(
            'all_cache',
            'select',
            [
                'name' => 'all_cache',
                'label' => '<strong>' . __('All Cache') . '</strong>',
                'value' => 1,
                'options' => [
                    '' => __('No change'),
                    'refresh' => __('Refresh'),
                    'disable' => __('Disable'),
                    'enable' => __('Enable'),
                ]
            ]
        );

        foreach ($this->cacheTypeList->getTypeLabels() as $type => $label) {
            $fieldset->addField(
                'enable_' . $type,
                'checkbox',
                [
                    'name' => 'enable[' . $type . ']',
                    'label' => __($label),
                    'value' => 1,
                    'checked' => (int)$this->_cacheState->isEnabled($type)
                ]
            );
        }
        $this->setForm($form);
        return $this;
    }
}
