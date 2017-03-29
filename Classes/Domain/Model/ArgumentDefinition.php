<?php
namespace Neos\DocTools\Domain\Model;

/*
 * This file is part of the Neos.DocTools package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * @todo document
 */
class ArgumentDefinition
{
    /**
     * Name of the argument
     *
     * @var string
     */
    protected $name;

    /**
     * Type of the argument
     *
     * @var string
     */
    protected $type;

    /**
     * Description of the argument
     *
     * @var string
     */
    protected $description;

    /**
     * Is argument required?
     *
     * @var boolean
     */
    protected $required = false;

    /**
     * Default value of the argument
     *
     * @var mixed
     */
    protected $defaultValue = null;

    /**
     * Constructor for this argument definition.
     *
     * @param string $name Name of argument
     * @param string $type Type of argument
     * @param string $description Description of argument
     * @param boolean $required TRUE if argument is required
     * @param mixed $defaultValue Default value
     */
    public function __construct($name, $type, $description, $required, $defaultValue = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->required = $required;
        $this->defaultValue = $defaultValue;
    }

    /**
     * Returns the name of the argument
     *
     * @return string Name of argument
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the type of the argument
     *
     * @return string Type of argument
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the description of the argument
     *
     * @return string Description of argument
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns whether the argument is required or optional
     *
     * @return boolean TRUE if argument is optional
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Returns the default value, if set
     *
     * @return mixed Default value
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
