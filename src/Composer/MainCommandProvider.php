<?php
/**
 * Date: 13/05/2018
 */

namespace leadingfellows\Composer;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


class MainCommandProvider implements CommandProviderCapability
{
    public function getCommands()
    {
        return [
            new testCommand,
            new runDrushCommand
        ];
    }
}

class testCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('leadingfellows-test');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->plugin->test();
    }
}

class runDrushCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('run-drush')
            ->setDescription('run a drush command')
            ->setHelp(<<<EOT
run drush command in a drupal site
<comment>%command.full_name% --  ARG1 ARG2 ... ARGN</comment>
EOT
            )
            ->setDefinition(array(
                new InputOption('uri', 'u', InputOption::VALUE_OPTIONAL, 'Drupal url', null),
                new InputOption('site', 's', InputOption::VALUE_REQUIRED, 'Drupal site name', 'default'),
                new InputArgument('args', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, ''),
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $opts = $input->getOptions();
        $args = $input->getArguments();
        $this->plugin->runDrush($opts, $args["args"]);
    }
}