<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Helper;

/**
 * Encodes and decodes JSON and checks for errors on these operations
 */
class JsonSerializer
{
    /**
     * @var array JSON Error code to error message mapping
     */
    private $jsonErrorMessages = [
        JSON_ERROR_DEPTH => 'Maximum depth exceeded',
        JSON_ERROR_STATE_MISMATCH => 'State mismatch',
        JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
        JSON_ERROR_SYNTAX => 'Syntax error, invalid JSON',
    ];

    /**
     * Encode a string as a JSON object with error checking
     *
     * @param mixed $data
     * @return string
     * @throws \Exception
     */
    public function jsonEncode($data)
    {
        $ret = json_encode($data);
        $this->checkJsonError($data);

        // return the json String
        return $ret;
    }

    /**
     * Decode a JSON string with error checking
     *
     * @param string $data
     * @param bool $asArray
     * @throws \Exception
     * @return mixed
     */
    public function jsonDecode($data, $asArray = true)
    {
        $ret = json_decode($data, $asArray);
        $this->checkJsonError($data);

        // return the array
        return $ret;
    }

    /**
     * Checks for JSON error in the latest encoding / decoding and throws an exception in case of error
     *
     * @throws \Exception
     */
    private function checkJsonError()
    {
        $jsonError = json_last_error();
        if ($jsonError !== JSON_ERROR_NONE) {
            // find appropriate error message
            $message = 'Unknown JSON Error';
            if (isset($this->jsonErrorMessages[$jsonError])) {
                $message = $this->jsonErrorMessages[$jsonError];
            }

            throw new \Exception(
                'JSON Encoding / Decoding error: ' . $message . var_export(func_get_arg(0), true),
                $jsonError
            );
        }
    }
}
