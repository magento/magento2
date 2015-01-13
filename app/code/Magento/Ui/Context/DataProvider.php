<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Context;

use Magento\Ui\Component\AbstractView;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractView
{
    /**
     * @return string
     */
    public function getAsJson()
    {
        return $this->renderContext->getConfigBuilder()->toJson($this->renderContext->getStorage());
    }
}
