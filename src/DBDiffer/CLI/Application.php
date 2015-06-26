<?php

namespace jach\DBDiffer\CLI;

use SebastianBergmann\Version;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class Application extends BaseApplication
{
    public function __construct()
    {
        $version = new Version('0.0.1', dirname(dirname(dirname(__DIR__))));
        parent::__construct('dbdiffer', $version->getVersion());
    }
    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input)
    {
        // This should return the name of your command.
        return 'diff';
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new DBDCommand();

        return $defaultCommands;
    }

    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (!$input->hasParameterOption('--quiet')) {
            $output->write(
                sprintf(
                    "dbdiffer %s by Jacob Christiasnen.\n\n",
                    $this->getVersion()
                )
            );
        }
        if ($input->hasParameterOption('--version') ||
            $input->hasParameterOption('-V')) {
            exit;
        }
        parent::doRun($input, $output);
    }
}
