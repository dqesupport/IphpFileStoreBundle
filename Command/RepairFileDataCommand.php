<?php

namespace Iphp\FileStoreBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Iphp\FileStoreBundle\Mapping\PropertyMapping;
use Iphp\FileStoreBundle\Mapping\PropertyMappingFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsCommand(
    name: 'iphp:filestore:repair',
    description: 'Re-uploads files for existing entities to recover from rename/move drift',
)]
class RepairFileDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PropertyMappingFactory $mappingFactory,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('entity', null, InputOption::VALUE_REQUIRED, 'The entity class name')
            ->addOption('field', null, InputOption::VALUE_REQUIRED, 'The field with file data')
            ->addOption('maxresults', null, InputOption::VALUE_OPTIONAL, 'Max results limitation')
            ->addOption('webdir', null, InputOption::VALUE_OPTIONAL, 'Web dir for searching file')
            ->addOption('force', null, InputOption::VALUE_OPTIONAL, 'Force reupload and rename files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityFullName = $input->getOption('entity');
        $field = $input->getOption('field');
        $force = (bool) $input->getOption('force');
        $webDir = $this->getWebDir($input);
        $maxResults = $this->getMaxResults($input);

        $repository = $this->em->getRepository($entityFullName);

        foreach ($this->getEntityIds($entityFullName, $maxResults) as $pos => $row) {
            $entity = $repository->find($row['id']);
            $fileData = $entity->{'get' . ucfirst($field)}();

            if (!$fileData) {
                continue;
            }

            $fileNameByWebPath = $webDir . $fileData['path'];
            $fileNameByWebPathExists = file_exists($fileNameByWebPath);

            $resolvedFileName = $this->getMappingFromField($entity, $field)->resolveFileName($fileData['fileName']);
            $resolvedFileNameExists = file_exists($resolvedFileName) ? 'exists' : 'NO';

            if (!$fileNameByWebPathExists && !$resolvedFileNameExists) {
                $output->writeln("can't find file ");
                continue;
            }

            if ($fileNameByWebPathExists && $resolvedFileNameExists && !$force) {
                continue;
            }

            $file = new UploadedFile(
                $fileNameByWebPathExists ? $fileNameByWebPath : $resolvedFileNameExists,
                $fileData['originalName'],
                $fileData['mimeType'],
                null,
                true,
            );

            $entity->{'set' . ucfirst($field)}($file);

            $this->em->persist($entity);

            if ($pos % 20 === 0) {
                $this->em->flush();
            }
            if ($pos % 100 === 0) {
                $this->em->clear();
            }
        }

        $this->em->flush();

        return Command::SUCCESS;
    }

    private function getWebDir(InputInterface $input): string
    {
        $webDir = $input->getOption('webdir');
        if ($webDir) {
            return $webDir;
        }

        return str_replace('\\', '/', $this->projectDir . '/public');
    }

    private function getMaxResults(InputInterface $input): int
    {
        $maxResults = $input->getOption('maxresults');
        if (!$maxResults || !is_numeric($maxResults)) {
            return 1000;
        }

        return (int) $maxResults;
    }

    private function getMappingFromField(object $entity, string $field): ?PropertyMapping
    {
        return $this->mappingFactory->getMappingFromField($entity, new \ReflectionClass($entity), $field);
    }

    private function getEntityIds(string $entityFullName, int $maxResults): array
    {
        return $this->em->createQuery(
            'SELECT e.id FROM ' . $entityFullName . ' e ORDER BY e.id ASC'
        )->setMaxResults($maxResults)->getArrayResult();
    }
}
