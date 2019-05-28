<?php

namespace jach\DBDiffer\CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use SebastianBergmann\Timer\Timer;

class DBDCommand extends Command
{
    protected function configure()
    {
        $this->setName('diff')
            ->setDescription('Diff SQL schema against a database')
            ->addOption(
                'configuration',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Configuration file'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('configuration')) {
            $configFile = $input->getOption('configuration');
        } else {
            $configFile = 'config.php';
        }
        require($configFile);

        $dbLocal = new \PDO($config['database']['temp']['dsn'], $config['database']['temp']['username'], $config['database']['temp']['password']);
        $dbRemote = new \PDO($config['database']['master']['dsn'], $config['database']['master']['username'], $config['database']['master']['password']);
        
        $dbCreator = new \jach\DBDiffer\SQL\DBCreator($dbLocal, $config['sqlDir']);
        try {
            $dbCreator->create();
        } catch (\RuntimeException $e) {
            $output->writeln('');
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            exit(1);
        }
        
        $dbObjDiffer = new \jach\DBDiffer\SQL\DBObjectDiffer($dbLocal, $dbRemote);
        
        $progress = new ProgressBar($output, count($dbCreator->getStmtArr()));
        $errors = array_reduce(
            $dbCreator->getStmtArr(),
            function ($result, $stmt) use ($progress, $dbObjDiffer) {
                $stmtErrors = $dbObjDiffer->diff($stmt);
                $progress->advance();
                return array_merge($result, $stmtErrors);
            },
            []
        );
        $progress->finish();

        $dbCreator->drop();

        if (count($errors) > 0) {
            $output->writeln("");
            foreach ($errors as $error) {
                $output->writeln("<error>{$error}</error>");
            }
            exit(1);
        }
        $output->writeln("\n<fg=black;bg=green>It's all OK</fg=black;bg=green>\n");
        $output->writeln(Timer::resourceUsage());
    }
}
