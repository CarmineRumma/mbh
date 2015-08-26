<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\UserBundle\Document\Group;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Group controller.
 * @Route("management/group")
 */
class GroupController extends Controller
{

    /**
     * Lists all entities.
     *
     * @Route("/", name="group")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHUserBundle:Group')->createQueryBuilder('q')
            ->sort('name', 'asc')
            ->getQuery()
            ->execute()
        ;

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="group_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function newAction()
    {
        $form = $this->createForm('mbh_bundle_userbundle_grouptype', null);

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="group_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHUserBundle:Group:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Group('new');
        $form = $this->createForm('mbh_bundle_userbundle_grouptype', $entity);
        $form->submit($request);

        if ($form->isValid()) {

            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.group.create_success'));

            return $this->afterSaveRedirect('group', $entity->getId());
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="group_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     * @ParamConverter(name="entity", class="MBHUserBundle:Group")
     */
    public function editAction(Group $entity)
    {

        $form = $this->createForm('mbh_bundle_userbundle_grouptype', $entity);

        return array(
            'form' => $form->createView(),
            'entity' => $entity,
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="group_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHUserBundle:Group:edit.html.twig")
     * @ParamConverter(name="entity", class="MBHUserBundle:Group")
     */
    public function updateAction(Request $request, Group $entity)
    {
        $form = $this->createForm('mbh_bundle_userbundle_grouptype', $entity);
        $form->submit($request);

        if ($form->isValid()) {

            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.group.update_success'));

            return $this->afterSaveRedirect('group', $entity->getId());
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="group_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHUserBundle:Group', 'group');
    }

}
