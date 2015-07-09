<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

/**
 * Class Form
 */
class Form extends AbstractComponent
{
    const NAME = 'form';

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSourceData()
    {
        $dataSource = [];
        $id = $this->getContext()->getRequestParam($this->getContext()->getDataProvider()->getRequestFieldName());

        if ($id) {
            $this->getContext()->getDataProvider()
                ->addFilter($this->getContext()->getDataProvider()->getPrimaryFieldName(), $id);
        }
        $data = $this->getContext()->getDataProvider()->getData();

        if (isset($data[$id])) {
            $dataSource = [
                'data' => $data[$id]
            ];
        }

        return $dataSource;
    }
}
