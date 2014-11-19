<?php

namespace Ens\JobeetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ens\JobeetBundle\Entity\Category;
use Symfony\Component\HttpFoundation\Request;

/**
 * Category controller.
 *
 */
class CategoryController extends Controller
{

    public function showAction(Request $request, $slug, $page)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var $category Ens\JobeetBundle\Entity\Category */
        $category = $em->getRepository('EnsJobeetBundle:Category')->findOneBySlug($slug);

        if (!$category) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $totalJobs = $em->getRepository('EnsJobeetBundle:Job')->countActiveJobs($category->getId());
        $jobsPerPage = $this->container->getParameter('max_jobs_on_category');
        $lastPage = ceil($totalJobs / $jobsPerPage);

        $jobs = $em->getRepository('EnsJobeetBundle:Job')->getActiveJobs($category->getId());
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $jobs,
            $page/*page number*/,
            $jobsPerPage/*limit per page*/
        );

        $totalPages = ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage());
        $format = $request->getRequestFormat();

        return $this->render('EnsJobeetBundle:Category:show.'.$format.'.twig', array(
            'category' => $category,
            'pagination' => $pagination,
            'totalJobs' => $totalJobs,
            'totalPages' => $totalPages,
            'feedId' => sha1($this->get('router')->generate('EnsJobeetBundle_category', array('slug' =>  $category->getSlug(), '_format' => 'atom'), true)),
        ));
    }

}