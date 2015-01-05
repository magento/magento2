<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
