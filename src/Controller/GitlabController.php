<?php

namespace App\Controller;

use App\GitlabImportServiceInterface;
use Gitlab\Client;
use Gitlab\Exception\RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Updates\SiteUpdateManager;


class GitlabController extends AbstractController {

  /**
   * @var \Gitlab\Client
   * The Gitlab client.
   */
  private $client;

  /**
   * @Route("/", name="projects")
   */
  public function projects(GitlabImportServiceInterface $harvest2GitlabImporterService, $gitUrl, $gitToken) {
    $client = $this->getClient($gitUrl, $gitToken);
    $projects = $client->projects()->all();
    return $this->render('/projects.html.twig', [
      'projects' => $projects,
    ]);
  }

  /**
   * @Route("/project/{projectId}/milestones", name="milestones")
   */
  public function milestones($projectId, $gitUrl, $gitToken) {
    $client = $this->getClient($gitUrl,$gitToken);
    $milestones = $client->milestones()->all($projectId);
    $currentProject = $client->projects()->show($projectId);

    return $this->render('/milestones.html.twig', [
        'milestones' => $milestones,
        'current_project' => $currentProject,
        'project_id' => $projectId
    ]);
  }

  /**
   * @Route("/project/{projectId}/milestones/{milestoneId}/issues", name="issues")
   */
  public function issues($projectId, $milestoneId, $gitUrl, $gitToken) {
    $client = $this->getClient($gitUrl, $gitToken);
    $milestoneIssues = $client->milestones()->issues($projectId, $milestoneId);
    $currentMilestone = $client->milestones()->show($projectId, $milestoneId);
    $totalEstimateTime = 0;
    $totalSpentTime = 0;
    foreach ($milestoneIssues as $milestoneIssue) {
      if (count($milestoneIssues) >= 1) {
        $totalEstimateTime += $milestoneIssue['time_stats']['time_estimate'];
      }
      if (count($milestoneIssues) >= 1) {
        $totalSpentTime += $milestoneIssue['time_stats']['total_time_spent'];
      }

    }
    return $this->render('/issues.html.twig', [
        'milestone_issues' => $milestoneIssues,
        'current_milestone' => $currentMilestone,
        'total_estimate_time' => gmdate("H:i",$totalEstimateTime),
        'total_spent_time' => gmdate("H:i",$totalSpentTime),
        'project_id' => $projectId,
        'milestone_id' => $milestoneId
    ]);
  }

  /**
   * @Route("/project/{projectId}/import", name="project_import")
   */
  public function import(GitlabImportServiceInterface $harvest2GitlabImporterService, $projectId, $gitUrl, $gitToken) {
    try {
      $project = $this->getClient($gitUrl, $gitToken)->projects()->show($projectId);
    }
    catch (RuntimeException $e) {
      $client = $this->getClient($gitUrl, $gitToken);
      $projects = $client->projects()->all();
      return $this->render('/project-not-found.html.twig', [
        'pid_not_found' => $projectId,
        'projects' => $projects,
      ])->setStatusCode(404, 'Project not found');
    }

    $harvest2GitlabImporterService->importTimeEntries($project["path_with_namespace"]);

    return Response::create('asdf');
    //return $this->redirect('http://harvest2gitlab.docksal/project/132/');
  }

  /**
   * Returns the Gitlab client.
   *
   * @param string $gitUrl
   * See GIT_URL.
   * @param string $gitToken
   * See GIT_TOKEN.
   *
   * @return \Gitlab\Client
   * Client.
   */
  protected function getClient($gitUrl, $gitToken) {
    if (empty($this->client)) {
      $this->client = Client::create($gitUrl)->authenticate($gitToken, Client::AUTH_URL_TOKEN);
    }
    return $this->client;
  }

  /*public function new(SiteUpdateManager $siteUpdateManager)
  {
    // ...

    if ($siteUpdateManager->notifyOfSiteUpdate()) {
      $this->addFlash('success', 'Notification mail was sent successfully.');
    }

    // ...
  }*/
}