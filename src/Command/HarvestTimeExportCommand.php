<?php

namespace App\Command;

use App\Service\HarvestServiceInterface;
use App\Service\ImportServiceInterface;
use Http\Client\Common\Exception\ClientErrorException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HarvestTimeExportCommand extends Command {
  /**
   * @var string
   * The name of the command (the part after "bin/console")
   */
  protected static $defaultName = 'app:harvest:time-export';

  /**
   * @var ImportServiceInterface
   * The import service.
   */
  private $import_service;

  /**
   * @var HarvestServiceInterface
   * The harvest service.
   */
  private $harvest_service;

  public function __construct(ImportServiceInterface $import_service, HarvestServiceInterface $harvestService, ?string $name = null) {
    $this->import_service = $import_service;
    $this->harvest_service = $harvestService;

    parent::__construct($name);
  }

  protected function configure() {
    $this
      // the short description shown while running "php bin/console list"
      ->setDescription('Exports time entries from harvest to gitlab issues.')

      // the full command description shown when running the command with
      // the "--help" option
      ->setHelp('This will export time entries from Harvest with references to Gitlab issues and update the time spend of each issue.')
      ->addArgument('harvest project id', InputArgument::OPTIONAL, 'the harvest project id')
      ->addArgument('harvest project code', InputArgument::OPTIONAL, 'the harvest project code')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $project_id = $input->getArgument('harvest project id');
    $project_code = $input->getArgument('harvest project code');


//    $this->harvest_service->getProjectByCode("keytec/harvest2gitlab");

    $io = new SymfonyStyle($input, $output);
    $io->title('Export time entries from harvest');

    if ($project_id == NULL && $project_code == NULL) {
      $io->error('No project id or code found');
      $io->text('Please add your Hubspot project id or code in order to start the sync');
      exit;
    }

    $project = NULL;

    if ($project_id != NULL) {
      try {
        $project = $this->harvest_service->getProjectById($project_id);
      }
      catch (ClientErrorException $e) {
        $io->warning('No harvest project found for id ' . $project_id);
      }
    }


    if ($project == NULL && $project_code != NULL) {
      try {
        $io->text('Trying to find project code, this may take a while...');

        $project = $this->harvest_service->getProjectByCode($project_code);
        $project_id = $project->getId();
      }
      catch (ClientErrorException $e) {
        $io->error('No harvest project found for code ' . $project_code);
        $io->text('Aborting.');
        exit;
      }
    }


    $project_name = $project->getCode() ? '[' . $project->getCode() . '] ' : '';
    $project_name .= $project->getName();

    $io->section('Project: ' . $project_name);

    $info = $this->import_service->importTimeEntries($project_id);
    $infos = ['Issues updated' => $info[1], 'Issues not found' => $info[2]];

    $io->text('Issues with references found: ' . $info[0]);
    if (!$input->getOption('verbose')) {
      foreach ($infos as $label => $info) {
        $io->text($label . ': ' . count($info));
      }
    }else {
      foreach ($infos as $label => $info) {
        $io->section($label . ': ' . count($info));
        $lines = [];
        foreach ($info as $item) {
          foreach ($item as $key => $value) {
            if (empty(trim($value)) || strpos($key, 'service') !== FALSE) {
              unset($item[$key]);
            }
          }
          $lines[] = implode(' | ', $item);
        }
        $io->listing($lines);
      }
    }

    $io->text('Done.');
  }
}