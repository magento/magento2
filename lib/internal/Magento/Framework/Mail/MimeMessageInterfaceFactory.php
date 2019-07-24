<?php
/**
 * Copyright (c) 2019 TechDivision GmbH
 * All rights reserved
 *
 * This product includes proprietary software developed at TechDivision GmbH, Germany
 * For more information see https://www.techdivision.com/
 *
 * To obtain a valid license for using this software please contact us at
 * license@techdivision.com
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class MimeMessageInterfaceFactory
 */
class MimeMessageInterfaceFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var string
     */
    private $_instanceName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = MimeMessageInterface::class
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Creates MimeMessageInterface instance with specified parameters
     *
     * @param array $data
     * @return MimeMessageInterface
     */
    public function create(array $data = []): MimeMessageInterface
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
