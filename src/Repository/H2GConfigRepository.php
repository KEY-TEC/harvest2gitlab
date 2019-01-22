<?php

namespace App\Repository;

use App\Entity\H2GConfig;
use App\Service\GitlabServiceInterface;
use App\Service\HarvestServiceInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method H2GConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method H2GConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method H2GConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class H2GConfigRepository extends ServiceEntityRepository {

  /**
   * @var \App\Service\HarvestServiceInterface
   */
  private $harvest_service;

  /**
   * @var \App\Service\GitlabServiceInterface
   */
  private $gitlab_service;

  /**
   * @var array
   */
  private $harvestProjects = [];

  /**
   * @var array
   */
  private $gitlabProjects = [];

  /**
   * H2GConfigRepository constructor.
   * @param RegistryInterface $registry
   * @param \App\Service\HarvestServiceInterface $harvestService
   *   Harvest Service.
   * @param \App\Service\GitlabServiceInterface $gitlabService
   *   Gitlab Service.
   */
  public function __construct(RegistryInterface $registry, HarvestServiceInterface $harvestService, GitlabServiceInterface $gitlabService) {
      parent::__construct($registry, H2GConfig::class);

      $this->harvest_service = $harvestService;
      $this->gitlab_service = $gitlabService;
  }

  /**
   * {@inheritdoc}
   */
  public function findAll() {
    $results = parent::findAll();
    $this->loadDisplayNames($results);
    return $results;
  }

  public function loadDisplayNames($configs) {
    foreach ($configs as $config) {
      $this->loadDisplayName($config);
    }
  }

  public function loadDisplayName($config) {
    if (!isset($this->harvestProjects[$config->getHarvestId()])) {
      $harvestProject = $this->harvest_service->getHarvestProjectById($config->getHarvestId());
      $this->harvestProjects[$config->getHarvestId()] = !empty($harvestProject) ? '[' . $harvestProject->getCode() . '] ' . $harvestProject->getName() : '- NOT FOUND! -';
    }
    if (!isset($this->gitlabProjects[$config->getGitlabId()])) {
      $gitlabProject = $this->gitlab_service->getProject($config->getGitlabId());
      $this->gitlabProjects[$config->getGitlabId()] = !empty($gitlabProject) ? $gitlabProject["name_with_namespace"] : '- NOT FOUND! -';
    }

    $config->setHarvestDisplayName($this->harvestProjects[$config->getHarvestId()]);
    $config->setGitlabDisplayName($this->gitlabProjects[$config->getGitlabId()]);

    return $config;
  }

  // /**
  //  * @return H2GConfig[] Returns an array of H2GConfig objects
  //  */
  /*
  public function findByExampleField($value)
  {
      return $this->createQueryBuilder('h')
          ->andWhere('h.exampleField = :val')
          ->setParameter('val', $value)
          ->orderBy('h.id', 'ASC')
          ->setMaxResults(10)
          ->getQuery()
          ->getResult()
      ;
  }
  */

  /*
  public function findOneBySomeField($value): ?H2GConfig
  {
      return $this->createQueryBuilder('h')
          ->andWhere('h.exampleField = :val')
          ->setParameter('val', $value)
          ->getQuery()
          ->getOneOrNullResult()
      ;
  }
  */
}
