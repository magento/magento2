<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form;

interface LabelAwareInterface
{
    /**
     * Set the label (if any) used for this element
     *
     * @param  $label
     * @return ElementInterface
     */
    public function setLabel($label);

    /**
     * Retrieve the label (if any) used for this element
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set the attributes to use with the label
     *
     * @param array $labelAttributes
     * @return self
     */
    public function setLabelAttributes(array $labelAttributes);

    /**
     * Get the attributes to use with the label
     *
     * @return array
     */
    public function getLabelAttributes();

    /**
     * Set many label options at once
     *
     * Implementation will decide if this will overwrite or merge.
     *
     * @param  array|\Traversable $arrayOrTraversable
     * @return self
     */
    public function setLabelOptions($arrayOrTraversable);

    /**
     * Get label specific options
     *
     * @return array
     */
    public function getLabelOptions();

     /**
     * Set a single label optionn
     *
     * @param  string $key
     * @param  mixed  $value
     * @return Element|ElementInterface
     */
    public function setLabelOption($key, $value);

    /**
     * Retrieve a single label option
     *
     * @param  $key
     * @return mixed|null
     */
    public function getLabelOption($key);

    /**
     * Remove a single label option
     *
     * @param string $key
     * @return ElementInterface
     */
    public function removeLabelOption($key);

    /**
     * Does the element has a specific label option ?
     *
     * @param  string $key
     * @return bool
     */
    public function hasLabelOption($key);

    /**
     * Remove many attributes at once
     *
     * @param array $keys
     * @return ElementInterface
     */
    public function removeLabelOptions(array $keys);

    /**
     * Clear all label options
     *
     * @return Element|ElementInterface
     */
    public function clearLabelOptions();
}
