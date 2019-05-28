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
        
        $dbManager = new \jach\DBDiffer\SQL\DBManager($dbLocal);
        $stmtArr = (new \jach\DBDiffer\SQL\DBStatementCollector($config['sqlDir']))->collect();
        try {
            $dbManager->create($stmtArr);
        } catch (\RuntimeException $e) {
            $output->writeln('');
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            exit(1);
        }
        
        $dbObjDiffer = new \jach\DBDiffer\SQL\DBObjectDiffer($dbLocal, $dbRemote);
        
        $dbObjDifferErrorArr = [];
        $progress = new ProgressBar($output, count($dbCreator->getStmtArr()));
        foreach ($dbCreator->getStmtArr() as $stmt) {
            $dbObjDifferResult = $dbObjDiffer->diff($stmt);
            if(!$dbObjDifferResult->success) {
                $dbObjDifferErrorArr[] = $dbObjDifferResult;
            }
            $progress->advance();
        }
        $progress->finish();

        $dbManager->drop();

        if (count($dbObjDifferErrorArr) > 0) {
            $output->writeln("");
            foreach ($dbObjDifferErrorArr as $dbObjDifferResult) {
                $output->writeln("<error>{$dbObjDifferResult->message}</error>");
            }
            exit(1);
        }
        $output->writeln("\n<fg=black;bg=green>It's all OK</fg=black;bg=green>\n");
        $output->writeln(Timer::resourceUsage());
    }
}
