<?php

namespace App\Controller;

use App\Service\ImportServiceInterface;
use App\Service\GitlabServiceInterface;
use Gitlab\Exception\RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Updates\SiteUpdateManager;

/**
 * Class GitlabController.
 *
 * @package App\Controller
 */
class GitlabController extends AbstractController {

  /**
   * Displays all projects.
   *
   * @Route("/", name="projects")
   */
  public function projects(GitlabServiceInterface $gitlabService) {
    $projects = $gitlabService->getProjects();
    return $this->render('/projects.html.twig', [
      'projects' => $projects['Projects'],
    ]);
  }

  /**
   * Displays all milestones of a project.
   *
   * @Route("/project/{projectId}/milestones", name="milestones")
   */
  public function milestones(GitlabServiceInterface $gitlabService, $projectId) {
    $milestones = $gitlabService->getMilestones($projectId);
    $currentProject = $gitlabService->getProject($projectId);
    return $this->render('/milestones.html.twig', [
      'milestones' => $milestones,
      'current_project' => $currentProject,
      'project_id' => $projectId,
    ]);
  }

  /**
   * Displays all issues of a milestone.
   *
   * @Route("/project/{projectId}/milestones/{milestoneId}/issues", name="issues")
   */
  public function milestoneIssues(GitlabServiceInterface $gitlabService, $projectId, $milestoneId) {
    $milestoneIssues = $gitlabService->getMilestoneIssues($projectId, $milestoneId);
    $currentMilestone = $gitlabService->getCurrentMilestone($projectId, $milestoneId);
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
      'total_estimate_time' => $totalEstimateTime,
      'total_spent_time' => $totalSpentTime,
      'project_id' => $projectId,
      'milestone_id' => $milestoneId,
    ]);
  }

  /**
   * Activates Gitlab import and displays status.
   *
   * @Route("/project/{projectId}/import", name="project_import")
   */
  public function import(GitlabServiceInterface $gitlabService, ImportServiceInterface $harvest2GitlabImporterService, $projectId) {
    try {
      $project = $gitlabService->getProject($projectId);
    }
    catch (RuntimeException $e) {
      $projects = $gitlabService->getProjects();
      return $this->render('/project-not-found.html.twig', [
        'pid_not_found' => $projectId,
        'projects' => $projects,
      ])->setStatusCode(404, 'Project not found');
    }
    $updated_time_entries = $harvest2GitlabImporterService->importTimeEntries($project["path_with_namespace"]);
    return Response::create('Project successfully updated<br><br><b>' . $updated_time_entries . '</b> Time Entries have been updated');
  }

}
