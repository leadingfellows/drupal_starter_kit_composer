<?php
/**
 * Date: 13/05/2018
 */

namespace leadingfellows\Composer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends \Composer\Command\BaseCommand {

    protected $output;

    protected $plugin;

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->output = $output;
        $this->plugin = $this->getMainPlugin();
    }
    // https://symfony.com/doc/current/console/coloring.html
    protected function log($message) {
        $this->output->writeln('<info>'.$this->getName().'</info> '.$message);
    }

    protected function getMainPlugin() {
        $composer = $this->getComposer();
        $pluginManager = $composer->getPluginManager();
        $plugins = $pluginManager->getPlugins();
        $class_to_find = "leadingfellows\Composer\MainPlugin";
        foreach($plugins as $key => $plugin) {
            if (get_class($plugin) == $class_to_find) {
                return $plugin;
            }
        }
        throw new \Exception("internal error, plugin '".$class_to_find."' not found!");
    }
}