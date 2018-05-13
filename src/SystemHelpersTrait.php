<?php
/**
 * Date: 13/05/2018
 */

namespace leadingfellows;

// see https://github.com/consolidation/config
//use Robo\Robo;
//use Robo\ResultData;
//use Symfony\Component\Process\Exception\ProcessFailedException;
// see https://github.com/webmozart/path-util
use Webmozart\PathUtil\Path;
// see https://symfony.com/doc/current/components/process.html
use Symfony\Component\Process\Process;

trait SystemHelpersTrait
{
    protected function _findExecutable($name, $logger = null) {
        $escaped_name = str_replace("'","",$name); // TODO : make it nicer..
        try {
            $result = $this->_executeCommand(array("which","'".$escaped_name."'"), ["show_output_on_error" => false], $logger);
            $output = $result->getOutput();
            $output_first_line = trim(explode("\n", trim($output))[0]);
            if ((strlen($output_first_line) == 0)) {
                $output_first_line = Path::canonicalize($output_first_line);
            }
            if ((strlen($output_first_line) == 0) || !file_exists($output_first_line)) {
                throw new \Exception("executable '".$escaped_name."' not found");
            }
            return $output_first_line;
        } catch(\Exception $ee) {
            throw new \Exception("executable '".$escaped_name."' not found");
        }
    }
    protected function _executeCommand(
        $command_array,
        $execute_options = [],
        $logger = null) {
        $execute_options = array_merge([
            "verbose" => false,
            "log" => false,
            "disable_output" => false,
            "throw_exception_on_error" => true,
            "timeout" =>0,
            "show_output_on_error" => true
        ], $execute_options);
        /*
        // using robo taskExecStack, not suitable to have stderr...!

        $this->say("Execute command: ".$command);
        $result = $this->taskExecStack()
            ->printOutput(false)
            ->silent(true)
            ->exec($command)
            ->run();
        */
        $verbose = $execute_options["verbose"]? true:false;
        $command = implode(" ", $command_array);
        if ($logger && $verbose) {
            $logger->notice("execute command: ".$command);
        }
        $process = new Process($command);
        if ($execute_options["disable_output"]) {
            $process->disableOutput();
        }
        if ($execute_options["timeout"] > 0) {
            $process->setTimeout((int)$execute_options["timeout"]);
        }
        if ($execute_options["log"]) {
            /*
            if ($logger && $verbose) {
                $logger->notice("[command log activated]");
            }
            */
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    echo $buffer;
                } else {
                    echo $buffer;
                }
            });
        } else {
            $process->run();
        }
        // getErrorOutput()
        // getOutput()
        if ($execute_options["throw_exception_on_error"] && !$process->isSuccessful()) {
            if($logger && $execute_options["show_output_on_error"]) {
                $logger->info($process->getOutput());
                $logger->error($process->getErrorOutput());
            }
            throw new \Exception("error running command ".$command);
        } else {
            if ($verbose) {
                //$this->logger->success("ok");
            }
        }
        if ($logger && $verbose) {
            $logger->notice("command executed: ".$command);
        }
        return $process;
    }
    /**
     * Helper method to remove directories and the files they contain.
     *
     * @param string $path
     *   The directory or file to remove. It must exist.
     *
     * @return bool
     *   TRUE on success or FALSE on failure.
     */
    protected static function deleteRecursive($path)
    {
        if (is_file($path) || is_link($path)) {
            return unlink($path);
        }
        $success = true;
        $dir = dir($path);
        while (($entry = $dir->read()) !== false) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $entry_path = $path . DIRECTORY_SEPARATOR . $entry;
            $success = static::deleteRecursive($entry_path) && $success;
        }
        $dir->close();
        return rmdir($path) && $success;
    }

    /**
     * Helper method to remove directories and the files they contain.
     *
     * @param string $path
     *   The directory or file to remove. It must exist.
     *
     * @return bool
     *   TRUE on success or FALSE on failure.
     */
    protected static function listFiles($path, $recursive=false, $logger=null)
    {
        if (is_file($path) || is_link($path)) {
            throw new \Exception("input path is a file: '" . $path . "'");
        } else {
            if ($logger) {
                $logger->notice("list files of ".$path);
            }
        }

        $returned = [];
        $dir = dir($path);
        $toexplore = [];
        while (($entry = $dir->read()) !== false) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $entry_path = $path . DIRECTORY_SEPARATOR . $entry;
            if (is_file($entry_path) || is_link($entry_path)) {
                $returned [] = $entry_path;
            } else if ($recursive) {
                $toexplore [] = $entry_path;
            }
        }
        $dir->close();
        foreach($toexplore as $subdir) {
            $returned = array_merge($returned, static::listFiles($subdir, $recursive));
        }
        return $returned;
    }
}