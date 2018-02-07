<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Ui\Component\Product\Form\Element;

use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Framework\Data\ValueSourceInterface;

/**
 * Class UseConfigSettings sets default value from configuration
 */
class UseConfigSettings extends Checkbox
{
    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (
            isset($config['keyInConfiguration'])
            && isset($config['valueFromConfig'])
            && $config['valueFromConfig'] instanceof ValueSourceInterface
        ) {
            $keyInConfiguration = $config['valueFromConfig']->getValue($config['keyInConfiguration']);
            if (!empty($config['unserialized']) && strpos($keyInConfiguration, 'a:') === 0) {
                $config['valueFromConfig'] = unserialize($keyInConfiguration);
            } else {
                $config['valueFromConfig'] = $keyInConfiguration;
            }
        }
        $this->setData('config', (array)$config);

        parent::prepare();
    }
}
