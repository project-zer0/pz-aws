<?php

declare(strict_types=1);

namespace ProjectZer0\PzAWS\Command;

use ProjectZer0\Pz\Console\Command\PzCommand;
use ProjectZer0\Pz\Process\DockerProcess;
use ProjectZer0\Pz\Process\Process;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Aurimas Niekis <aurimas@niekis.lt>
 */
class AWSLoginCommand extends PzCommand
{
    protected function configure(): void
    {
        $this
            ->setName('aws:login')
            ->setDescription('Configure AWS and login into Docker AWS ECR Registry');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $imageName = $this->getConfiguration()['aws']['image'] ?? 'amazon/aws-cli';
        $configDir = $this->getConfiguration()['aws']['config_dir'] ?? '$PZ_PWD/.pz/.aws';

        $io = new SymfonyStyle($input, $output);

        $io->section('Please login to AWS');

        $process = new DockerProcess(
            $imageName,
            ['configure'],
            interactive: true,
            cleanUp: true,
            exec: false
        );

        $process->addVolume($configDir, '/root/.aws');

        if (0 !== $process->execute()) {
            $io->error('aws configure failed');

            return 1;
        }

        $io->newLine(2);
        $io->section('Fetching AWS Identity');

        $process = new DockerProcess(
            $imageName,
            ['sts', 'get-caller-identity'],
            interactive: false,
            cleanUp: true,
            exec: false
        );

        $process->addVolume($configDir, '/root/.aws');
        $sfProcess = $process->getProcess()->getProcess();
        $sfProcess->setTty(false);

        if (0 !== $sfProcess->run()) {
            $io->error('Fetching Identity Failed');
            $io->write($sfProcess->getOutput());
            $io->write($sfProcess->getErrorOutput());

            return 1;
        }

        $identity = json_decode($sfProcess->getOutput(), true, flags: JSON_THROW_ON_ERROR);

        $process = new DockerProcess(
            $imageName,
            ['configure', 'get', 'region'],
            interactive: false,
            cleanUp: true,
            exec: false
        );

        $process->addVolume($configDir, '/root/.aws');
        $sfProcess = $process->getProcess()->getProcess();
        $sfProcess->setTty(false);

        if (0 !== $sfProcess->run()) {
            $io->error('Fetching Configuration Region Failed');
            $io->write($sfProcess->getOutput());
            $io->write($sfProcess->getErrorOutput());

            return 1;
        }

        $identity['Region'] = rtrim($sfProcess->getOutput());

        $io->section('Saving identity to .pz/.aws/identity.json');

        file_put_contents('/project/.pz/.aws/identity.json', json_encode($identity, JSON_PRETTY_PRINT));

        $io->section('Logging into Docker AWS ECR Registry');

        $process = new DockerProcess(
            $imageName,
            ['ecr', 'get-login-password'],
            interactive: false,
            cleanUp: true,
            exec: false
        );

        $process->addVolume($configDir, '/root/.aws');
        $sfProcess = $process->getProcess()->getProcess();
        $sfProcess->setTty(false);

        if (0 !== $sfProcess->run()) {
            $io->error('Fetching get-login-password Failed');
            $io->write($sfProcess->getOutput());
            $io->write($sfProcess->getErrorOutput());

            return 1;
        }

        $password = rtrim($sfProcess->getOutput());

        $url = 'https://' . $identity['Account'] . '.dkr.ecr.' . $identity['Region'] . '.amazonaws.com';

        $process = new Process(
            DockerProcess::BINARY,
            [
                'login',
                '--username',
                'AWS',
                '--password',
                $password,
                $url,
            ]
        );

        $sfProcess = $process->getProcess();
        $sfProcess->setTty(false);

        if (0 !== $sfProcess->run()) {
            $io->error('Docker Login Failed');
            $io->write($sfProcess->getOutput());
            $io->write($sfProcess->getErrorOutput());

            return 1;
        }

        $io->success('Finished');

        return 0;
    }
}
