<?php

namespace Movieaster\MovieManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Movieaster\MovieManagerBundle\Entity\Path;
use Movieaster\MovieManagerBundle\Form\PathType;

/**
 * Path controller.
 *
 * @Route("/path")
 */
class PathController extends Controller
{
    /**
     * Lists all Path entities.
     *
     * @Route("/", name="path")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MovieasterMovieManagerBundle:Path')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a Path entity.
     *
     * @Route("/{id}/show", name="path_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MovieasterMovieManagerBundle:Path')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Path entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Path entity.
     *
     * @Route("/new", name="path_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Path();
        $form   = $this->createForm(new PathType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new Path entity.
     *
     * @Route("/create", name="path_create")
     * @Method("POST")
     * @Template("MovieasterMovieManagerBundle:Path:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Path();
        $form = $this->createForm(new PathType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('path_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Path entity.
     *
     * @Route("/{id}/edit", name="path_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MovieasterMovieManagerBundle:Path')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Path entity.');
        }

        $editForm = $this->createForm(new PathType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Path entity.
     *
     * @Route("/{id}/update", name="path_update")
     * @Method("POST")
     * @Template("MovieasterMovieManagerBundle:Path:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MovieasterMovieManagerBundle:Path')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Path entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new PathType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('path_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Path entity.
     *
     * @Route("/{id}/delete", name="path_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MovieasterMovieManagerBundle:Path')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Path entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('path'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
