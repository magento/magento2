<?php
namespace Magento\Framework\Setup;

/**
 * Select config option in deployment config
 */
class SelectConfigOption extends AbstractConfigOption
{
    /**#@+
     * Frontend input types
     */
    const FRONTEND_WIZARD_RADIO = 'radio';
    const FRONTEND_WIZARD_SELECT = 'select';
    /**#@- */

    /**
     * Available options
     *
     * @var array
     */
    private $selectOptions;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $frontendType
     * @param array $selectOptions
     * @param string $description
     * @param string $default
     * @param string|null $shortCut
     */
    public function __construct(
        $name,
        $frontendType,
        array $selectOptions,
        $description = '',
        $default = '',
        $shortCut = null
    ) {
        if ($frontendType != self::FRONTEND_WIZARD_SELECT && $frontendType != self::FRONTEND_WIZARD_RADIO) {
            throw new \InvalidArgumentException('Frontend input type has to be select or radio.');
        }
        if (!$selectOptions) {
            throw new \InvalidArgumentException('Select options can\'t be empty.');
        }
        $this->selectOptions = $selectOptions;
        parent::__construct(
            $name,
            $frontendType,
            $description,
            $default,
            self::VALUE_REQUIRED,
            $shortCut
        );
    }

    /**
     * Get available options
     *
     * @return array
     */
    public function getSelectOptions()
    {
        return $this->selectOptions;
    }

    /**
     * Validates input data
     *
     * @param mixed $data
     * @return void
     */
    public function validate($data)
    {
        if (!in_array($data, $this->getSelectOptions())) {
            throw new \InvalidArgumentException("Value specified for '{$this->getName()}' is not supported: '{$data}'");
        }
        parent::validate($data);
    }
}
