<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto\Test\Unit\Code\Generator\Mock;

class SampleDto
{
    /**
     * @var string
     */
    private $param1;

    /**
     * @var string
     */
    private $param2;

    /**
     * @var string
     */
    private $param3;

    /**
     * @param string $param1
     * @param string $param2
     * @param string $param3
     */
    public function __construct(
        string $param1,
        string $param2,
        string $param3
    ) {
        $this->param1 = $param1;
        $this->param2 = $param2;
        $this->param3 = $param3;
    }

    /**
     * @return string
     */
    public function getParam1(): string
    {
        return $this->param1;
    }

    /**
     * @return string
     */
    public function getParam2(): string
    {
        return $this->param2;
    }

    /**
     * @return string
     */
    public function getParam3(): string
    {
        return $this->param3;
    }
}
