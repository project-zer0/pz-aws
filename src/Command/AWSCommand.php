<?php

declare(strict_types=1);

namespace ProjectZer0\PzAWS\Command;

use ProjectZer0\Pz\Console\Command\ProcessCommand;
use ProjectZer0\Pz\Process\DockerProcess;
use ProjectZer0\Pz\Process\ProcessInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aurimas Niekis <aurimas@niekis.lt>
 */
class AWSCommand extends ProcessCommand
{
    protected function configure(): void
    {
        $this
            ->setName('aws:cli')
            ->setAliases(['aws'])
            ->setDescription('The  AWS  Command  Line  Interface is a unified tool to manage your AWS services.');
    }

    public function getProcess(
        array $processArgs,
        InputInterface $input,
        OutputInterface $output
    ): ProcessInterface {
        $imageName = $this->getConfiguration()['aws']['image'] ?? 'amazon/aws-cli';
        $configDir = $this->getConfiguration()['aws']['config_dir'] ?? '$PZ_PWD/.pz/.aws';

        return (new DockerProcess(
            $imageName,
            $processArgs,
            interactive: true,
            cleanUp: true,
            workDir: '/project',
            exec: true
        ))->addVolume('$PZ_PWD', '/project')
            ->addVolume($configDir, '/root/.aws');
    }
}
