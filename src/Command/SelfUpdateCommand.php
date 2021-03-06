<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Command;

use CacheTool\Util\ManifestUpdateStrategy;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setAliases(['selfupdate'])
            ->setDescription('Updates cachetool.phar to the latest version')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $updater = new Updater(null, false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $updater->getStrategy()->setPackageName('gordalina/cachetool');
        $updater->getStrategy()->setPharName('cachetool.phar');
        $updater->getStrategy()->setCurrentLocalVersion('@package_version@');

        if (!$updater->hasUpdate()) {
            $output->writeln(sprintf('You are already using the latest version: <info>%s</info>', $this->getApplication()->getVersion()));
            return 0;
        }

        try {
            $output->writeln(sprintf('Updating to version <info>%s</info>', $updater->getNewVersion()));

            if (!$updater->update()) {
               throw new Exception("Failed to update");
            }

            $output->writeln('<info>Updated successfully</info>');
        } catch (\Exception $e) {
            $updater->rollback();
            $output->writeln(sprintf('An error ocurred during the update process, rolled back to version <info>%s</info>', $updater->getOldVersion()));

            return 1;
        }

        return 0;
    }
}
