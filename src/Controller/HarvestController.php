<?php

namespace App\Controller;

use App\Service\HarvestServiceInterface;
use App\Service\ImportServiceInterface;
use Http\Client\Common\Exception\ClientErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Updates\SiteUpdateManager;

/**
 * Class GitlabController.
 *
 * @package App\Controller
 */
class HarvestController extends AbstractController {

  /**
   * Displays all projects.
   *
   * @Route("/", name="harvest_projects")
   */
  public function projects(Request $request, HarvestServiceInterface $harvestService) {
    $page = !empty($request->query->get('page')) ? $request->query->get('page') : 1;
    if (!is_int($page)) {
      $page = 1;
    }

    $projects = $harvestService->getProjects(['page' => $page]);
    return $this->render('/projects.html.twig', [
      'projects' => $projects,
    ]);
  }

  /**
   * Activates Gitlab import and displays status.
   *
   * @Route("/harvest/{projectId}/export", name="harvest_export")
   */
  public function export(HarvestServiceInterface $harvestService, ImportServiceInterface $harvest2GitlabImporterService, $projectId) {
    try {
      $info = $harvest2GitlabImporterService->importTimeEntries($projectId);
      return $this->render('/project-export.html.twig', [
        'project' => $harvestService->getProjectById($projectId),
        'updated' => $info[0],
        'all' => $info[1],
        'failed' => $info[2],
      ]);
    }
    catch (ClientErrorException $e) {
      $projects = $harvestService->getProjects();
      return $this->render('/project-not-found.html.twig', [
        'pid_not_found' => $projectId,
        'projects' => $projects,
      ])->setStatusCode(404, 'Project not found');
    }
  }

}
