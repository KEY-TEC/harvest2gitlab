<?php

namespace App\Controller;

// Use the composer autoloader to load dependencies

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\HarvestImportServiceInterface;

class HarvestImport extends AbstractController {

  /**
   * @Route("/project/{project_id}/import")
   */
  public function import(HarvestImportServiceInterface $service) {

    $service->getTimeEntries('keytec/gitlab2harvest', [
      'keytec/gitlab2harvest#1',
      'keytec/gitlab2harvest#2',
      'keytec/gitlab2harvest#3',
    ]
    );
    $timeEntries = $service->fetchAllTimeEntries('keytec/gitlab2harvest', 1);
    var_dump($timeEntries);

    return $this->redirect('http://harvest2gitlab.docksal/project/132/');
  }
}
