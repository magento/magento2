<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldFrontendUi\Component;

use Magento\Ui\Component\AbstractComponent;

/**
 * Extra comment saver ui component.
 */
class CommentSaver extends AbstractComponent
{

    const NAME = 'comment_saver';

    /**
     * Gets component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Gets data source data
     *
     * @return array
     */
    public function getDataSourceData()
    {
        return ['data' => $this->getContext()->getDataProvider()->getData()];
    }
}
