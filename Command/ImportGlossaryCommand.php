<?php

// src/Command/ImportGlossaryCommand.php

namespace TeiEditionBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Spatie\SimpleExcel\SimpleExcelReader;

/**
 * Import Glossary from data/glossary.xlsx (one line per item and language).
 */
class ImportGlossaryCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName('import:glossary')
            ->setDescription('Import Glossary')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fname = 'glossary.xlsx';

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

        $termRepository = $this->em->getRepository('\TeiEditionBundle\Entity\GlossaryTerm');

        $count = 0;
        SimpleExcelReader::create($fname)->getRows()
        ->each(function (array $row) use ($output, $termRepository, &$count) {
            $unique_values = array_unique(array_values($row));
            if (1 == count($unique_values) && null === $unique_values[0]) {
                // all values null
                return;
            }

            if (empty($row['term']) || empty($row['language']) || !in_array($row['language'], [ 'deu', 'eng' ])) {
                return;
            }

            $output->writeln('Insert/Update: ' . $row['term']);

            $term = $termRepository->findOneBy([
                'term' => $row['term'],
                'language' => $row['language'],
            ]);

            if (is_null($term)) {
                $term = new \TeiEditionBundle\Entity\GlossaryTerm();
                $term->setTerm(trim($row['term']));
                $term->setSlug($this->slugify->slugify($term->getTerm()));
                $term->setLanguage($row['language']);
            }

            foreach ($row as $key => $value) {
                switch ($key) {
                    case 'name':
                    case 'headline':
                    case 'description':
                    case 'url':
                        $method = 'set' . ucfirst($key);
                        $term->{$method}($value);
                        break;

                    default:
                        // $output->writeln('Skip : ' . $key);
                }
            }

            $this->em->persist($term);
            ++$count;
        });

        if ($count > 0) {
            $this->flushEm($this->em);
        }

        return Command::SUCCESS;
    }
}
