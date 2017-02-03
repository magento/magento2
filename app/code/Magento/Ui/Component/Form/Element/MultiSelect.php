<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Component\Form\Element;

class MultiSelect extends Select
{
    const NAME = 'multiselect';

    const DEFAULT_SIZE = 6;

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $config['size'] = self::DEFAULT_SIZE;
        $this->setData('config', array_replace_recursive((array)$this->getData('config'), $config));
        parent::prepare();
    }
}
