<?php
/**
 * Interface for a test service for error handling testing
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule3\Service\V1;

interface ErrorInterface
{
    /**
     * @return \Magento\TestModule3\Service\V1\Entity\Parameter
     */
    public function success();

    /**
     * @return int Status
     */
    public function resourceNotFoundException();

    /**
     * @return int Status
     */
    public function serviceException();

    /**
     * @return int Status
     */
    public function authorizationException();

    /**
     * @return int Status
     */
    public function webapiException();

    /**
     * @return int Status
     */
    public function otherException();

    /**
     * @return int Status
     */
    public function returnIncompatibleDataType();

    /**
     * @param \Magento\TestModule3\Service\V1\Entity\WrappedErrorParameter[] $wrappedErrorParameters
     * @return int Status
     */
    public function inputException($wrappedErrorParameters);
}
