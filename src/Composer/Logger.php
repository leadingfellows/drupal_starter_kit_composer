<?php
/**
 * Date: 13/05/2018
 */

namespace leadingfellows\Composer;
//use Wikimedia\Composer\Logger;
class Logger extends \Wikimedia\Composer\Logger
{
    public function comment($message)
    {
        if ($this->inputOutput->isVerbose()) {
            $message = $this->name?
                "<comment>[{$this->name}]</comment> {$message}"
                : "{$message}";
            $this->log($message);
        }
    }
    public function error($message)
    {
        $message =  $this->name?
            "<error>[{$this->name}]</error> {$message}"
            : "{$message}";
        $this->log($message);
    }
    public function message($message)
    {
        $message =  $this->name?
            "<info>[{$this->name}]</info> {$message}"
            : "{$message}";
        $this->log($message);
    }
}