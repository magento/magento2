<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Controller\Result;

use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\Controller\AbstractResult;

/**
 * A result that contains raw response - may be good for passing through files,
 * returning result of downloads or some other binary contents
 *
 * @api
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
    protected function render(HttpResponseInterface $response)
    {
        $response->setBody($this->contents);
        return $this;
    }
}
