<?php
/**
 * Date: 13/05/2018
 */

namespace leadingfellows\Composer;

// see https://github.com/webmozart/path-util
use Webmozart\PathUtil\Path;

class MainPlugin extends \leadingfellows\Composer\BasePlugin {


    public function getDrupalUri() {
        $merged_config = $this->getConfig();
        if (array_key_exists("drupal", $merged_config)
            && array_key_exists("uri", $merged_config["drupal"])) {
            return trim($merged_config["drupal"]["uri"]);
        }
        return "http://127.0.0.1";
    }

    public function runDrush($opts, $arguments) {
        $sitename = $opts["site"];
        $url = $opts["uri"];
        if (!$url) {
            $url = $this->getDrupalUri();
        }
        $verbose = $opts["verbose"];
        $drupalRoot = $this->getDrupalRoot();
        $site_dir = Path::join($drupalRoot, "sites",  $sitename);
        if (!file_exists($site_dir)) {
            throw new \Exception("site directory '" . $site_dir . "' does not exist");
        }
        $bin_dir = $this->getBinDir();
        $drush_path =  Path::join($bin_dir, "drush");
        $command = $drush_path;
        if ($verbose) {
            $command .= " -v";
        }
        // multi-site selection is made through URL...
        if ($url && strlen($url) > 0) {
            $command .= " --uri=".$url;
        }
        $command .= " --root=".$drupalRoot; //$site_dir;
        $command .= " ".implode(" ",$arguments);
        $cmd_result = $this->executeCommand($command);
        $code = $cmd_result["code"];
        $command_output = $cmd_result["out"];
        if (strlen(trim($command_output)) > 0) {
            $this->logger->message($cmd_result["out"]);
        }
        //echo print_r($cmd_result, TRUE);
    }

    public function test() {

        if (0) {
            // php version
            $cmd = $this->getPhp() . " --version";
            $cmd_result = $this->executeCommand($cmd);
            $this->logger->message("PHP version: " . $cmd_result["out"]);

            // get composer config
            $conf = $this->composer->getConfig();
            //$this->logger->message(var_dump($conf->raw()));
            //$this->logger->message(var_dump($conf->all()));
        }
        // get options
        $this->logger->message("Your options");
        $res = $this->getOption();
        $this->logger->message(print_r($res, TRUE));

        $this->logger->message("Your configuration");
        // get config
        $res = $this->getConfig();
        $this->logger->message(print_r($res, TRUE));
    }

}



