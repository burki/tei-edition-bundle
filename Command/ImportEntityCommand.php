<?php
// src/Command/ImportEntityCommand.php

namespace TeiEditionBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Spatie\SimpleExcel\SimpleExcelReader;

/**
 * Import Entities from data/gnd2tgn.xlsx (currently only places).
 */
class ImportEntityCommand
extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('import:entity')
            ->setDescription('Import Entities')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $fname = 'gnd2tgn.xlsx';

        try {
            $fname = $this->locateData($fname);
        } catch (\InvalidArgumentException $e) {
            $output->writeln(sprintf('<error>%s does not exist</error>', $fname));

            return Command::FAILURE;
        }

        $fs = new Filesystem();

        if (!$fs->exists($fname)) {
            $output->writeln(sprintf('<error>%s does not exist</error>', $fname));

            return Command::FAILURE;
        }


        $entities = [ 'place' => [] ];
        SimpleExcelReader::create($fname)->getRows()
        ->each(function (array $row) use (&$entities) {
            $unique_values = array_unique(array_values($row));
            if (1 == count($unique_values) && null === $unique_values[0]) {
                // all values null
                return;
            }

            if (empty($row['tgn'])) {
                return;
            }

            if (array_key_exists('', $row)) {
                unset($row['']);
            }

            $entities['place'][$row['tgn']] = $row;
        });

        foreach ([ 'person', 'place', 'organization' ] as $type) {
            // currently only place
            if (empty($entities[$type])) {
                continue;
            }

            foreach ($entities[$type] as $uri => $additional) {
                switch ($type) {
                    case 'person':
                        $this->insertMissingPerson($uri);
                        break;

                    case 'place':
                        $this->insertMissingPlace($uri, $additional);
                        break;

                    case 'organization':
                        $this->insertMissingOrganization($uri);
                        break;
                }
            }
        }

        return Command::SUCCESS;
    }
}
