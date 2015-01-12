<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Container;

use Magento\Ui\Component\AbstractView;

/**
 * Class Content
 */
class Content extends AbstractView
{
    /**
     * Prepare component data
     *
     * @return $this|void
     */
    public function prepare()
    {
        $configData = $this->getDefaultConfiguration();
        if ($this->hasData('config')) {
            $configData = array_merge($configData, $this->getData('config'));
        }

        $this->prepareConfiguration($configData);
    }
}
