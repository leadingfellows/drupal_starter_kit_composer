<?php
/**
 * Date: 13/05/2018
 */

namespace leadingfellows\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capable;
// see https://github.com/webmozart/path-util
use Webmozart\PathUtil\Path;
use Composer\Util\ProcessExecutor;
// see https://symfony.com/doc/current/components/process.html
use Symfony\Component\Process\Process;
// see https://symfony.com/doc/current/components/filesystem.html
use Symfony\Component\Filesystem\Filesystem;
// see https://github.com/webflo/drupal-finder
use DrupalFinder\DrupalFinder;


class MainPlugin implements PluginInterface, Capable {


    use \leadingfellows\SystemHelpersTrait;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    protected $io;
    /**
     * @var Options
     */
    private $options;

    /**
     * @var Logger $logger
     */
    protected $logger;

    /**
     * @var ProcessExecutor $executor
     */
    protected $executor;
    /**
     * @var string $rootdir
     */
    protected $rootdir;

    /**
     * @var Filesystem $fs
     */
    protected $fs;


    /**
     * Called at activation of this plugin
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->options = new \leadingfellows\Composer\Options($composer);
        $this->logger = new Logger('', $io);
        $this->executor = new ProcessExecutor($this->io);
        $this->rootdir = empty($_SERVER['PWD']) ? getcwd() : $_SERVER['PWD'];
        $this->fs = new Filesystem();
        //if ($this->io->isVeryVerbose() || $this->io->isDebug())
        $this->logger->debug("activate composer plugin leadingfellows/drupal_starter_kit_project");
    }
    /**
     * get option value for this plugin
     * (options are configured in the extra section of composer.json)
     *
     * @param string $option_key Key for the option
     *
     * @return mixed option value
     */
    public function getOption($option_key = '') {
        $option_value = $this->options->get($option_key);
        return $option_value;
    }

    /**
     * Provide capabilities:
     *   - composer custom commands
     */
    public function getCapabilities()
    {
        return array(
            'Composer\Plugin\Capability\CommandProvider' => 'leadingfellows\Composer\MainCommandProvider',
        );
    }

    /**
     * Get the path of the PHP executable
     *
     * @return string path of the PHP executable
     */
    protected function getPhp() {
        if (defined('PHP_BINARY')) {
            return PHP_BINARY;
        } else {
            return getenv('PHP_COMMAND');
        }
    }

    /**
     * Get the absolute path of a file or directory
     *
     * @param string $relative_path Path relative to the root directory
     *
     * @return string absolute path
     */
    protected function getAbspath($relative_path) {
        return Path::join($this->rootdir, $relative_path);
    }
    /**
     * Get the path of the vendor directory
     *
     * @return string path of the bin directory
     */
    protected function getVendorDir() {
        $vendorDir = $this->composer->getConfig()->get('vendor-dir');
        return $vendorDir; //$this->getAbspath($vendorDir);
    }
    /**
     * Get the path of the bin directory
     *
     * @return string path of the bin directory
     */
    protected function getBinDir() {
        $binDir = $this->composer->getConfig()->get('bin-dir');
        return $binDir; //$this->getAbspath($binDir);
    }

    public function getDrupalRoot() {
        /*
            $config_parser = new \leadingfellows\utils\ConfigParser();
            //$res = $config_parser->addYaml($path);
            $merged_config  = $config_parser->toArray();
            if (array_key_exists("drupal", $merged_config)
                && array_key_exists("webroot", $merged_config["drupal"])) {
                $drupal_webroot = trim($merged_config["drupal"]["webroot"]);
                return $this->getAbspath($drupal_webroot);
            }
        */
        // fallback to custom finder
        $drupalFinder = new DrupalFinder();
        $drupalFinder->locateRoot($this->rootdir);
        $drupalRoot = $drupalFinder->getDrupalRoot();
        if ($drupalRoot == "") {
            return $this->rootdir;
        }
        return $drupalRoot;
    }

    protected function extractPhpSource($input_file) {
        $source = file_get_contents($input_file);
        $lines = explode(PHP_EOL, $source);
        $tokens = token_get_all($source);
        $discarded_tokens = array("T_CLOSE_TAG", "T_OPEN_TAG");
        $discarded_lines = array();
        foreach ($tokens as $token) {
            if (is_array($token)) {
                $tn = token_name($token[0]);
                if (in_array($tn, $discarded_tokens)) {
                    $discarded_lines[$token[2] -1] = $token[2] - 1;
                }
            }
        }
        $output_lines = array();
        foreach( $lines as $linen => $line) {
            if (array_key_exists($linen, $discarded_lines)) {
                continue;
            }
            $trimed = trim($line);
            if (strlen($trimed) > 0) {
                $output_lines[] = $trimed;
            }
        }
        $output_code = implode(PHP_EOL, $output_lines);
        return $output_code;
    }

    public function runDrush($opts, $arguments) {
        $sitename = $opts["site"];
        $url = $opts["uri"];
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

        // execute a command
        $cmd = $this->getPhp()." --version";
        $cmd_result = $this->executeCommand($cmd);
        $this->logger->message("PHP version: ".print_r($cmd_result,TRUE));

        // get config
        $conf =$this->composer->getConfig();
        //$this->logger->message(var_dump($conf->raw()));
        $this->logger->message(var_dump($conf->all()));
    }

}



