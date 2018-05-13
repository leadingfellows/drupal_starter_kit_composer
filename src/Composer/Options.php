<?php
/**
 * Date: 13/05/2018
 */

namespace leadingfellows\Composer;

class Options {

    private $composer;

    protected $base_key;

    public function __construct(\Composer\Composer $composer)
    {
        $this->composer = $composer;
        $this->base_key = "leadingfellows";
    }
    public function get($key = '')
    {
        // default options
        $default_options = [];
        $extra = $this->composer->getPackage()->getExtra() + [$this->base_key => $default_options];
        /*
            // hard-coded options
            $extra[$this->base_key] += [
                'param' => 'hello world',
            ];
        */
        return $key ? $extra[$this->base_key][$key] : $extra[$this->base_key];
    }
}