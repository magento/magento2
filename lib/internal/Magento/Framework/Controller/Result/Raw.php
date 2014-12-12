<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Controller\Result;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\AbstractResult;

/**
 * A result that contains raw response - may be good for passing through files,
 * returning result of downloads or some other binary contents
 */
class Raw extends AbstractResult
{
    /**
     * @var string
     */
    protected $contents;

    /**
     * @param string $contents
     * @return $this
     */
    public function setContents($contents)
    {
        $this->contents = $contents;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function render(ResponseInterface $response)
    {
        $response->setBody($this->contents);
        return $this;
    }
}
