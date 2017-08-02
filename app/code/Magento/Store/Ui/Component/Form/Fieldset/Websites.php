<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Ui\Component\Form\Fieldset;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Ui\Component\Form\Fieldset;

/**
 * Class Websites Fieldset
 * @since 2.1.0
 */
class Websites extends Fieldset
{
    /**
     * Store manager
     *
     * @var StoreManager
     * @since 2.1.0
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param StoreManager $storeManager
     * @param UiComponentInterface[] $components
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        ContextInterface $context,
        StoreManager $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     * @since 2.1.0
     */
    public function prepare()
    {
        parent::prepare();
        if ($this->storeManager->isSingleStoreMode()) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }
}
