<?php

namespace App\Controller;

use App\Entity\H2GConfig;
use App\Form\H2GConfigType;
use App\Repository\H2GConfigRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/h2/g/config")
 */
class H2GConfigController extends AbstractController
{
  /**
   * @Route("/", name="h2_g_config_index", methods="GET")
   *
   * @return Response
   */
    public function index(H2GConfigRepository $h2GConfigRepository): Response {
      return $this->render('h2_g_config/index.html.twig', ['h2_g_configs' => $h2GConfigRepository->findAll()]);
    }

    /**
     * @Route("/new", name="h2_g_config_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $h2GConfig = new H2GConfig();
        $form = $this->createForm(H2GConfigType::class, $h2GConfig);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($h2GConfig);
            $em->flush();

            return $this->redirectToRoute('h2_g_config_index');
        }

        return $this->render('h2_g_config/new.html.twig', [
            'h2_g_config' => $h2GConfig,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="h2_g_config_show", methods="GET")
     */
    public function show(H2GConfig $h2GConfig, H2GConfigRepository $h2GConfigRepository): Response {
      return $this->render('h2_g_config/show.html.twig', ['h2_g_config' => $h2GConfigRepository->loadDisplayName($h2GConfig)]);
    }

    /**
     * @Route("/{id}/edit", name="h2_g_config_edit", methods="GET|POST")
     */
    public function edit(Request $request, H2GConfig $h2GConfig): Response
    {
        $form = $this->createForm(H2GConfigType::class, $h2GConfig);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('h2_g_config_index', ['id' => $h2GConfig->getId()]);
        }

        return $this->render('h2_g_config/edit.html.twig', [
            'h2_g_config' => $h2GConfig,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="h2_g_config_delete", methods="DELETE")
     */
    public function delete(Request $request, H2GConfig $h2GConfig): Response
    {
        if ($this->isCsrfTokenValid('delete'.$h2GConfig->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($h2GConfig);
            $em->flush();
        }

        return $this->redirectToRoute('h2_g_config_index');
    }
}
