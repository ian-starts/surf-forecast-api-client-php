<?php

namespace IanKok\SurfForecastApiClient\Entities;

class Country
{
    /**
     * @var string
     */
    protected  $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * Country constructor.
     *
     * @param string $name
     * @param string $value
     */
    public function __construct(string $name, string $value)
    {
        $this->name  = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

}