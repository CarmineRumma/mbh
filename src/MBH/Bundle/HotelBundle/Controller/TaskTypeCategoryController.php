<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Document\TaskTypeCategory;
use MBH\Bundle\HotelBundle\Form\TaskTypeCategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class TaskTypeCategoryController

 * @Route("/tasktype/category")
 */
class TaskTypeCategoryController extends Controller
{
    /**
     * @Route("/new", name="task_type_category_new")
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_TASK_TYPE_CATEGORY_NEW')")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new TaskTypeCategory();
        $form = $this->createForm(TaskTypeCategoryType::class, $entity, [
            'method' => Request::METHOD_POST
        ]);

        if($form->handleRequest($request)->isValid()){
            $entity->setIsSystem(false);
            $entity->setHotel($this->hotel);
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success',
                $this->get('translator')->trans('controller.taskTypeController.record_created_success'));

            return $this->isSavedRequest() ?
                $this->redirectToRoute('task_type_category_edit', ['id' => $entity->getId()]) :
                $this->redirectToRoute('tasktype', ['category' => $entity->getId()]);
        };

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="task_type_category_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TASK_TYPE_CATEGORY_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHHotelBundle:TaskTypeCategory")
     */
    public function editAction(TaskTypeCategory $entity, Request $request)
    {
        $form = $this->createForm(TaskTypeCategoryType::class, $entity, []);

        if($request->isMethod(Request::METHOD_POST)){
            if($form->handleRequest($request)->isValid()){
                //$entity->setIsSystem(false);
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.TaskTypeController.record_edited_success'));

                return $this->isSavedRequest() ?
                    $this->redirectToRoute('task_type_category_edit', ['id' => $entity->getId()]) :
                    $this->redirectToRoute('tasktype', ['category' => $entity->getId()]);
            };
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     *
     * @Route("/{id}/delete", name="task_type_category_delete")
     * @Method({"GET"})
     * @Security("is_granted('ROLE_TASK_TYPE_CATEGORY_DELETE')")
     * @ParamConverter("entity", class="MBHHotelBundle:TaskTypeCategory")
     */
    public function deleteAction(TaskTypeCategory $entity)
    {
        if($entity->isSystem()) {
            throw $this->createNotFoundException();
        }
        return $this->deleteEntity($entity->getId(), get_class($entity), 'tasktype');
    }
}