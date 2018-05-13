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
            $message = "<comment>[{$this->name}]</comment> {$message}";
            $this->log($message);
        }
    }
    public function error($message)
    {
        $message = "<error>[{$this->name}]</error> {$message}";
        $this->log($message);
    }
    public function message($message)
    {
        $message = "<info>[{$this->name}]</info> {$message}";
        $this->log($message);
    }
}