<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Document\BirthPlace;
use MBH\Bundle\PackageBundle\Document\DocumentRelation;
use MBH\Bundle\PackageBundle\Document\Migration;
use MBH\Bundle\PackageBundle\Document\UnwelcomeItem;
use MBH\Bundle\PackageBundle\Document\UnwelcomeRepository;
use MBH\Bundle\PackageBundle\Document\Visa;
use MBH\Bundle\PackageBundle\Form\TouristMigrationType;
use MBH\Bundle\PackageBundle\Form\TouristVisaType;
use MBH\Bundle\PackageBundle\Form\UnwelcomeItemType;
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
     * @Security("is_granted('ROLE_TOURIST_VIEW')")
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
     * @Security("is_granted('ROLE_TOURIST_VIEW')")
     * @Template()
     */
    public function jsonAction(Request $request)
    {
        $qb = $this->dm->getRepository('MBHPackageBundle:Tourist')
            ->createQueryBuilder('r')
            ->skip($request->get('start'))
            ->limit($request->get('length'))
            ->field('deletedAt')->equals(null)
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
     * @Security("is_granted('ROLE_TOURIST_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Tourist();
        $entity->setCommunicationLanguage($this->container->getParameter('locale'));
        $form = $this->createForm(new TouristType(), $entity, [
            'genders' => $this->container->getParameter('mbh.gender.types')
        ]);

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new entity via ajax.
     *
     * @Route("/create/ajax", name="tourist_create_ajax")
     * @Method("POST")
     * @Security("is_granted('ROLE_TOURIST_NEW')")
     */
    public function createAjaxAction(Request $request)
    {
        $entity = new Tourist();
        $entity->setDocumentRelation(new DocumentRelation());
        $entity->setBirthplace(new BirthPlace());
        $entity->setCitizenship($this->dm->getRepository('MBHVegaBundle:VegaState')->findOneByOriginalName('РОССИЯ'));
        $entity->getDocumentRelation()->setType('vega_russian_passport');

        $form = $this->createForm(new TouristType(), $entity,
            ['genders' => $this->container->getParameter('mbh.gender.types')]);
        $docForm = $this->createForm('mbh_document_relation', $entity);
        $addressForm = $this->createForm('mbh_address_object_decomposed', $entity->getAddressObjectDecomposed());

        $form->submit($request);
        $docForm->submit($request);
        $addressForm->submit($request);

        if ($form->isValid() && $docForm->isValid() && $addressForm->isValid()) {

            $entity->setAddressObjectDecomposed($addressForm->getData());
            $this->dm->persist($entity);
            $this->dm->flush();

            $text = $entity->getFullName();

            if ($entity->getBirthday() || $entity->getPhone() || $entity->getEmail()) {
                $pieces = [];
                !$entity->getBirthday() ?: $entity[] = $entity->getBirthday()->format('d.m.Y');
                !$entity->getPhone() ?: $pieces[] = $entity->getPhone();
                !$entity->getEmail() ?: $pieces[] = $entity->getEmail();
                $text .= ' (' . implode(', ', $pieces) . ')';
            }

            return new JsonResponse([
                'error' => false,
                'id' => $entity->getId(),
                'text' => $text
            ]);
        }
        $errors = [];
        foreach ($this->get('validator')->validate($entity) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse([
            'error' => true,
            'text' => implode('<br>', $errors)
        ]);
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="tourist_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_TOURIST_NEW')")
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
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
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
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function editAction(Tourist $entity)
    {
        $form = $this->createForm(new TouristType(), $entity, [
            'genders' => $this->container->getParameter('mbh.gender.types')
        ]);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/{id}/edit/document", name="tourist_edit_document")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
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

        $form = $this->createForm('mbh_document_relation', $entity, [
            'method' => Request::METHOD_PUT
        ]);

        if ($request->isMethod(Request::METHOD_PUT)) {
            $form->handleRequest($request);

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
     *
     * @Route("/{id}/edit/visa", name="tourist_edit_visa")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function editVisaAction(Tourist $entity, Request $request)
    {
        $entity->getMigration() ?: $entity->setMigration(new Migration());
        $entity->getVisa() ?: $entity->setVisa(new Visa());
        $entity->getVisa()->getType() ?: $entity->getVisa()->setType('visa');

        $form = $this->createFormBuilder($entity)
            ->add('migration', new TouristMigrationType())
            ->add('visa', new TouristVisaType())
            ->getForm();

        if ($request->isMethod(Request::METHOD_PUT)) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.touristController.record_edited_success'));

                return $this->isSavedRequest() ?
                    $this->redirectToRoute('tourist_edit_visa', ['id' => $entity->getId()]) :
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
     * @Route("/{id}/edit/unwelcome", name="tourist_edit_unwelcome")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function editUnwelcomeAction(Tourist $tourist, Request $request)
    {
        /** @var UnwelcomeRepository $unwelcomeRepository */
        $unwelcomeRepository = $this->get('mbh.package.unwelcome_repository');

        $blackListInfo = $unwelcomeRepository->findOneByTourist($tourist);
        $isInBlackList = isset($blackListInfo);

        if(!$isInBlackList) {
            $blackListInfo = new UnwelcomeItem();
        }

        $blackListInfo->setHotel($this->hotel);
        $blackListInfo->setTourist($tourist);

        $form = $this->createForm(new UnwelcomeItemType(), $blackListInfo, [
            'method' => Request::METHOD_PUT
        ]);

        $form->handleRequest($request);
        if($form->isValid()) {
            if($isInBlackList) {
                $unwelcomeRepository->update($blackListInfo);
            } else {
                $unwelcomeRepository->add($blackListInfo);
            }
            return $this->redirectToRoute('tourist_edit_unwelcome', ['id' => $tourist->getId()]);
        }

        return [
            'form' => $form->createView(),
            'isInBlackList' => $isInBlackList,
            'tourist' => $tourist,
            'logs' => $this->logs($tourist),
        ];
    }

    /**
     * @Route("/{id}/delete/black_list", name="tourist_delete_black_list")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function deleteBlackListAction(Tourist $tourist, Request $request)
    {
        /** @var BlackListRepository $blackListRepository */
        $blackListRepository = $this->get('mbh.package.black_list_repository');
        $blackListRepository->deleteByTourist($tourist);

        return $this->redirectToRoute('tourist_edit_black_list', ['id' => $tourist->getId()]);
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
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
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
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
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
     * @Security("is_granted('ROLE_TOURIST_DELETE')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHPackageBundle:Tourist', 'tourist');
    }

    /**
     * Get city by query
     *
     * @Route("/get/{id}/json", name="json_tourist", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_TOURIST_VIEW')")
     * @ParamConverter("tourist", class="MBHPackageBundle:Tourist")
     * @return JsonResponse
     */
    public function jsonEntryAction(Tourist $tourist)
    {
        return new JsonResponse($tourist);
    }

    /**
     * Get city by query
     *
     * @Route("/get/{id}", name="ajax_tourists", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_TOURIST_VIEW')")
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

                if ($payer->getBirthday() || $payer->getPhone() || $payer->getEmail()) {
                    $pieces = [];
                    !$payer->getBirthday() ?: $pieces[] = $payer->getBirthday()->format('d.m.Y');
                    !$payer->getPhone() ?: $pieces[] = $payer->getPhone();
                    !$payer->getEmail() ?: $pieces[] = $payer->getEmail();
                    $text .= ' (' . implode(', ', $pieces) . ')';
                }

                return new JsonResponse([
                    'id' => $payer->getId(),
                    'text' => $text
                ]);
            }
        }

        $regex = new \MongoRegex('/.*' . $request->get('query') . '.*/i');
        $qb = $this->dm->getRepository('MBHPackageBundle:Tourist')->createQueryBuilder('q')
            ->sort(['fullName' => 'asc', 'birthday' => 'desc'])
        ;
        $qb->addOr($qb->expr()->field('fullName')->equals($regex));
        $qb->addOr($qb->expr()->field('email')->equals($regex));
        $qb->addOr($qb->expr()->field('phone')->equals($regex));

        $payers = $qb->getQuery()->execute();

        $data = [];

        foreach ($payers as $payer) {
            $text = $payer->getFullName();
            if ($payer->getBirthday() || $payer->getPhone() || $payer->getEmail()) {
                $pieces = [];
                !$payer->getBirthday() ?: $pieces[] = $payer->getBirthday()->format('d.m.Y');
                !$payer->getPhone() ?: $pieces[] = $payer->getPhone();
                !$payer->getEmail() ?: $pieces[] = $payer->getEmail();
                $text .= ' (' . implode(', ', $pieces) . ')';
            }

            $data[] = [
                'id' => $payer->getId(),
                'text' => $text
            ];
        }

        return new JsonResponse(['results' => $data]);
    }
}
