<?php

namespace jach\DBDiffer\CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

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
            $configFile = ROOT . DIRECTORY_SEPARATOR . 'config.php';
        }
        require($configFile);

        $dir = new \DirectoryIterator($config['sqlDir']);

        $fileCount = (iterator_count($dir)-2);

        $progress = new ProgressBar($output, $fileCount);

        $dbLocal = new \PDO($config['database']['temp']['dsn'], $config['database']['temp']['username'], $config['database']['temp']['password']);
        $dbLocal->query('CREATE DATABASE __dbdiffer');
        $dbLocal->query('USE __dbdiffer');
        $localDbTableFetcher = new \jach\DBDiffer\SQLTableFetcher\DatabaseSQLTableFetcher($dbLocal);

        $dbRemote = new \PDO($config['database']['master']['dsn'], $config['database']['master']['username'], $config['database']['master']['password']);
        $remoteDbTableFetcher = new \jach\DBDiffer\SQLTableFetcher\DatabaseSQLTableFetcher($dbRemote);

        $sqlDiffer = new \jach\DBDiffer\SQLDiffer();

        $errors = [];
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $file = $fileinfo->openFile();
                $res1 = file_get_contents($fileinfo->getRealPath());
                $dbLocal->query($res1);

                $fileName = $fileinfo->getFilename();

                $tableName = substr($fileName, 0, -4);

                try {
                    $localDbTable = $localDbTableFetcher->fetch($tableName);
                } catch (\RuntimeException $e) {
                    $errors[] = $e->getMessage();
                    $progress->advance();
                    continue;
                }

                try {
                    $remoteDbTable = $remoteDbTableFetcher->fetch($tableName);
                } catch (\RuntimeException $e) {
                    $errors[] = $e->getMessage();
                    $progress->advance();
                    continue;
                }

                $res1 = $localDbTable->getStatement();
                $res2 = $remoteDbTable->getStatement();

                $aiRemover = new \jach\DBDiffer\SQL\Remover\AutoIncrementRemover();
                $ineRemover = new \jach\DBDiffer\SQL\Remover\IfNotExistsRemover();

                $res1 = $aiRemover->remove($res1);
                $res1 = $ineRemover->remove($res1);

                $res2 = $aiRemover->remove($res2);
                $res2 = $ineRemover->remove($res2);

                $res1 = trim($res1);
                $res2 = trim($res2);

                if (!$sqlDiffer->diff($res1, $res2)) {
                    $errors[] = "{$tableName} is not equal";
                    $tmpName1 = tempnam("/tmp", "FOO");
                    $tmpName2 = tempnam("/tmp", "FOO");
                    file_put_contents($tmpName1, $res1);
                    file_put_contents($tmpName2, $res2);
                    $errors[] = shell_exec("diff -u {$tmpName1} {$tmpName2}");
                    unlink($tmpName1);
                    unlink($tmpName2);
                }
                $progress->advance();
            }
        }
        $progress->finish();

        $dbLocal->query('DROP DATABASE __dbdiffer');
        if (count($errors) > 0) {
            $output->writeln("");
            foreach ($errors as $error) {
                $output->writeln("<error>{$error}</error>");
            }
            exit(1);
        }
        $output->writeln("\n<fg=black;bg=green>It's all OK</fg=black;bg=green>\n");
        $output->writeln(\PHP_Timer::resourceUsage());
    }
}
