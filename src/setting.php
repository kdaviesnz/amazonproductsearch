<?php

namespace kdaviesnz\amazon;

class Setting implements ISetting
{

    private $type = '';
    private $name = '';
    private $value;

    /**
     * Setting constructor.
     * @param string $type
     * @param string $name
     * @param $value
     */
    public function __construct( $type, $name, $value )
    {
        $this->type = $type;
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    public function save( $replace = true ) {

    	// @todo
        return true;
    }

    public function __toString() {
        return $this->value;
    }
}

