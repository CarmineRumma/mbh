<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Controller\HotelableControllerInterface;
use MBH\Bundle\HotelBundle\Document\TaskType;
use MBH\Bundle\HotelBundle\Document\TaskTypeCategory;
use MBH\Bundle\HotelBundle\Form\TaskTypeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class TaskTypeController
 * @package MBH\Bundle\HotelBundle\Controller
 * @Route("/tasktype")
 */
class TaskTypeController extends Controller implements HotelableControllerInterface
{
    /**
     *
     * @Route("/info/{category}", name="tasktype", defaults={"category"=null}, requirements={
     *    "category": "\w*"
     * })
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TASK_TYPE_VIEW')")
     * @Template()
     */
    public function indexAction(Request $request, $category = null)
    {
        if($category) {
            $category = $this->dm->getRepository('MBHHotelBundle:TaskTypeCategory')->find($category);
            if(!$category) {
                throw $this->createNotFoundException();
            }
        }

        /** @var TaskTypeCategory[] $taskTypeCategories */
        $taskTypeCategories = $this->dm->getRepository('MBHHotelBundle:TaskTypeCategory')->findAll();

        /** @var TaskTypeCategory $category */
        if(!$category) {
            $category = reset($taskTypeCategories);
        }

        $entity = new TaskType();

        $roles = array_keys($this->container->getParameter('security.role_hierarchy.roles'));
        $roles = array_combine($roles, $roles);
        $form = $this->createForm(TaskTypeType::class, $entity, [
            'roles' => $roles,
            'dm' => $this->dm
        ]);

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);

            if($form->isValid()) {
                $this->dm->persist($entity);
                $entity->setHotel($this->hotel);
                $this->dm->flush();

                $request->getSession()->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.taskTypeController.record_created_success'));
                return $this->redirectToRoute('tasktype', ['category' => $entity->getCategory()->getId()]);
            }
        }

        return [
            'form' => $form->createView(),
            'taskTypeCategories' => $taskTypeCategories,
            'activeCategory' => $category
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="tasktype_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TASK_TYPE_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHHotelBundle:TaskType")
     */
    public function editAction(Request $request, TaskType $entity)
    {
        $roles = array_keys($this->container->getParameter('security.role_hierarchy.roles'));
        $roles = array_combine($roles, $roles);

        $form = $this->createForm(TaskTypeType::class, $entity, [
            'scenario' => TaskTypeType::SCENARIO_EDIT,
            'roles' => $roles,
            'dm' => $this->dm
        ]);

        if($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.TaskTypeController.record_edited_success'))
                ;
                return $this->isSavedRequest() ?
                    $this->redirectToRoute('tasktype_edit', ['id' => $entity->getId()]) :
                    $this->redirectToRoute('tasktype', ['category' => $entity->getCategory()->getId()]);
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }


    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="tasktype_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_TASK_TYPE_DELETE')")
     * @ParamConverter("entity", class="MBHHotelBundle:TaskType")
     */
    public function deleteAction(TaskType $entity)
    {
        return $this->deleteEntity($entity->getId(), 'MBHHotelBundle:TaskType',
            'tasktype', ['category' => $entity->getCategory()->getId()]);
    }
}