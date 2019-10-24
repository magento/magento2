<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Ui\Component\Form\Field;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Customer\Helper\Address as AddressHelper;

/**
 * Process setting to set Default Value for Disable Automatic Group Changes Based on VAT ID
 *
 * Class \Magento\Customer\Ui\Component\Form\Field\DisableAutoGroupChange
 */
class DisableAutoGroupChange extends \Magento\Ui\Component\Form\Field
{
    /**
     * Yes value for Default Value for Disable Automatic Group Changes Based on VAT ID
     */
    const DISABLE_AUTO_GROUP_CHANGE_YES = '1';

    /**
     * Address Helper
     *
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param AddressHelper $addressHelper
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        AddressHelper $addressHelper,
        array $components = [],
        array $data = []
    ) {
        $this->addressHelper = $addressHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare component configuration
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        parent::prepare();

        if ($this->addressHelper->isDisableAutoGroupAssignDefaultValue()) {
            $currentConfig = $this->getData('config');
            $currentConfig['default'] = self::DISABLE_AUTO_GROUP_CHANGE_YES;
            $this->setData('config', $currentConfig);
        }
    }
}
