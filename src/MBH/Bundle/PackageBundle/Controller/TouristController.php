<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Document\BirthPlace;
use MBH\Bundle\PackageBundle\Document\DocumentRelation;
use MBH\Bundle\PackageBundle\Form\TouristExtendedType;
use MBH\Bundle\VegaBundle\Document\VegaFMS;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Form\TouristType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/tourist")
 */
class TouristController extends Controller
{
    /**
     * Lists all entities.
     *
     * @Route("/", name="tourist")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/json", name="tourist_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function jsonAction(Request $request)
    {
        $qb = $this->dm->getRepository('MBHPackageBundle:Tourist')
            ->createQueryBuilder('r')
            ->skip($request->get('start'))
            ->limit($request->get('length'))
            ->sort('fullName', 'asc');

        $search = $request->get('search')['value'];
        if (!empty($search)) {
            $qb->addOr($qb->expr()->field('fullName')->equals(new \MongoRegex('/.*' . $search . '.*/ui')));
        }

        $entities = $qb->getQuery()->execute();

        return [
            'entities' => $entities,
            'total' => $entities->count(),
            'draw' => $request->get('draw')
        ];
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="tourist_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Tourist();
        $form = $this->createForm(new TouristType(), $entity,
            ['genders' => $this->container->getParameter('mbh.gender.types')]);

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="tourist_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Tourist:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Tourist();
        $form = $this->createForm(new TouristType(), $entity,
            ['genders' => $this->container->getParameter('mbh.gender.types')]);

        $form->submit($request);
        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set('success',
                $this->get('translator')->trans('controller.touristController.record_created_success'));

            return $this->afterSaveRedirect('tourist', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
        ];
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}/edit", name="tourist_update")
     * @Method("PUT")
     * @Security("is_granted('ROLE_USER')")
     * @Template("MBHPackageBundle:Tourist:edit.html.twig")
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function updateAction(Request $request, Tourist $entity)
    {
        $form = $this->createForm(new TouristType(), $entity,
            ['genders' => $this->container->getParameter('mbh.gender.types')]);

        $form->submit($request);
        if ($form->isValid()) {

            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.touristController.record_edited_success'));

            return $this->afterSaveRedirect('tourist', $entity->getId());
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="tourist_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function editAction(Tourist $entity)
    {
        $form = $this->createForm(new TouristType(), $entity,
            ['genders' => $this->container->getParameter('mbh.gender.types')]);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/{id}/edit/document", name="tourist_edit_document")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function editDocumentAction(Tourist $entity, Request $request)
    {
        $entity->getBirthplace() ?: $entity->setBirthplace(new BirthPlace());
        $entity->getDocumentRelation() ?: $entity->setDocumentRelation(new DocumentRelation());

        //Default Value
        $entity->getCitizenship() ?: $entity->setCitizenship($this->dm->getRepository('MBHVegaBundle:VegaState')->findOneByOriginalName('РОССИЯ'));
        $entity->getDocumentRelation()->getType() ?: $entity->getDocumentRelation()->setType('vega_russian_passport');

        $form = $this->createForm('mbh_document_relation', $entity);

        if ($request->isMethod(Request::METHOD_PUT)) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.touristController.record_edited_success'));

                return $this->isSavedRequest() ?
                    $this->redirectToRoute('tourist_edit_document', ['id' => $entity->getId()]) :
                    $this->redirectToRoute('tourist');
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/regions", name="get_json_regions", options={"expose"=true})
     * @Method("GET")
     */
    public function ajaxRegionAction(Request $request)
    {
        $list = [];
        $criteria = [];
        if($value = $request->get('value')) {
            $criteria = ['title' => new \MongoRegex('/.*' . $value . '.*/ui')];
        }

        $entities = $this->dm->getRepository('MBHHotelBundle:Region')->findBy($criteria, ['title' => 1], 50);
        foreach ($entities as $entity) {
            $list[$entity->getId()] = $entity->getTitle();
        }
        return new JsonResponse(['data' => $list]);
    }
    /**
     * @Route("/authority_organ_json_list", name="authority_organ_json_list", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     */
    public function authorityOrganListAction(Request $request)
    {
        $list = [];
        if($query = $request->get('query')) {
            $repository = $this->dm->getRepository('MBHVegaBundle:VegaFMS');
            $entities = $repository->findBy(['name' => new \MongoRegex('/.*' . $query . '.*/ui')], ['name' => 1], 50);
            foreach ($entities as $entity) {
                $list[$entity->getId()] = $entity->getName();
            }
        }

        return new JsonResponse($list);
    }

    /**
     * @Route("/authority_organ/{id}", name="ajax_authority_organ", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("entity", class="MBHVegaBundle:VegaFMS")
     */
    public function authorityOrganAction(VegaFMS $entity)
    {
        return new JsonResponse([
            'id' => $entity->getId(),
            'text' => $entity->getName()
        ]);
    }

    /**
     * @Route("/{id}/edit/address", name="tourist_edit_address")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function editAddressAction(Tourist $entity, Request $request)
    {
        $form = $this->createForm('mbh_address_object_decomposed', $entity->getAddressObjectDecomposed());

        if ($request->isMethod(Request::METHOD_PUT)) {
            $form->submit($request);

            if ($form->isValid()) {
                $entity->setAddressObjectDecomposed($form->getData());
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.touristController.record_edited_success'));

                return $this->isSavedRequest() ?
                    $this->redirectToRoute('tourist_edit_address', ['id' => $entity->getId()]) :
                    $this->redirectToRoute('tourist');
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
     * @Route("/{id}/delete", name="tourist_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHPackageBundle:Tourist', 'tourist');
    }


    /**
     * Get city by query
     *
     * @Route("/get/{id}", name="ajax_tourists", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted(['ROLE_MANAGER', 'ROLE_BOOKKEEPER'])")
     * @return JsonResponse
     */
    public function ajaxListAction(Request $request, $id = null)
    {
        if (empty($id) && empty($request->get('query'))) {
            return new JsonResponse([]);
        }

        if (!empty($id)) {
            $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->find($id);
            if ($payer) {
                $text = $payer->getFullName();

                if (!empty($payer->getBirthday())) {
                    $text .= ' (' . $payer->getBirthday()->format('d.m.Y') . ')';
                }

                return new JsonResponse([
                    'id' => $payer->getId(),
                    'text' => $text
                ]);
            }
        }

        $payers = $this->dm->getRepository('MBHPackageBundle:Tourist')->createQueryBuilder('q')
            ->field('fullName')->equals(new \MongoRegex('/.*' . $request->get('query') . '.*/i'))
            ->sort(['fullName' => 'asc', 'birthday' => 'desc'])
            ->getQuery()
            ->execute();

        $data = [];

        foreach ($payers as $payer) {
            $text = $payer->getFullName();
            if (!empty($payer->getBirthday())) {
                $text .= ' (' . $payer->getBirthday()->format('d.m.Y') . ')';
            }

            $data[] = [
                'id' => $payer->getId(),
                'text' => $text
            ];
        }

        return new JsonResponse($data);
    }
}
