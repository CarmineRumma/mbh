<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\PackageBundle\Document\OrderRepository;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\DocumentGenerator\ChainGeneratorFactory;
use MBH\Bundle\PackageBundle\DocumentGenerator\Template\TemplateGeneratorFactory;
use MBH\Bundle\PackageBundle\DocumentGenerator\Xls\XlsGeneratorFactory;
use MBH\Bundle\PackageBundle\Form\OrderDocumentType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use MBH\Bundle\PackageBundle\Document\OrderDocument;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/")
 * @Method("GET")
 *
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class DocumentsController extends Controller
{
    /**
     * @param Request $request
     * @param Order $entity
     * @param Package $package
     * @return array|RedirectResponse
     * @Route("/{id}/documents/{packageId}", name="order_documents")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("order", class="MBHPackageBundle:Order")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @Template()
     */
    public function indexAction(Request $request, Order $entity, Package $package)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $orderDocument = new OrderDocument();
        $touristIds = $this->get('mbh.helper')->toIds($package->getTourists());

        if ($mainTourist = $package->getMainTourist()) {
            $touristIds[] = $mainTourist->getId();
        }

        $orderDocumentTypes = $this->container->getParameter('mbh.order.document.types');
        $vagaDocumentTypes = $this->container->get('mbh.vega.dictionary_provider')->getDocumentTypes();
        $docTypes = $orderDocumentTypes + $vagaDocumentTypes;

        $groupDocTypes = ['' => $orderDocumentTypes, 'Vega' => $vagaDocumentTypes];
        $scanTypes = $this->container->get('mbh.vega.dictionary_provider')->getScanTypes();

        $form = $this->createForm(new OrderDocumentType(), $orderDocument, [
            'documentTypes' => $groupDocTypes,
            'scanTypes' => $scanTypes,
            'touristIds' => $touristIds
        ]);

        if ($request->isMethod("PUT") && $permissions->check($entity)) {
            $form->submit($request);

            if ($form->isValid()) {
                $orderDocument->upload();
                $package->getOrder()->addDocument($orderDocument);
                $this->dm->persist($package);
                $this->dm->flush();

                return $this->redirect($this->generateUrl("order_documents",
                    ['id' => $entity->getId(), 'packageId' => $package->getId()]));
            }
        }

        return [
            'package' => $package,
            'entity' => $entity,
            'docTypes' => $docTypes,
            'form' => $form->createView(),
            'logs' => $this->logs($package),
        ];
    }

    /**
     * @param Order $entity
     * @param Package $package
     * @param $name
     * @return RedirectResponse
     *
     * @Route("/document/{id}/{packageId}/{name}/remove", name="order_remove_document", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("order", class="MBHPackageBundle:Order")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     */
    public function removeAction(Order $entity, Package $package, $name)
    {
        $permissions = $this->container->get('mbh.package.permissions');

        if (!$permissions->checkHotel($entity) || !$permissions->check($entity)) {
            throw $this->createNotFoundException();
        }

        $entity->removeDocumentByName($name);
        $this->dm->persist($entity);
        $this->dm->flush();

        return $this->redirect($this->generateUrl('order_documents',
            ['id' => $entity->getId(), 'packageId' => $package->getId()]));
    }


    /**
     *
     * @Route("/document/{name}/view/{download}", name="order_document_view", options={"expose"=true}, defaults={"download" = 0})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @param $name
     * @param $download
     * @return Response
     */
    public function viewAction($name, $download = 0)
    {
        /** @var OrderRepository $packageRepository */
        $orderRepository = $this->dm->getRepository('MBHPackageBundle:Order');
        /** @var Order $order */
        $order = $orderRepository->findOneBy(['documents.name' => $name]);

        if (!$order) {
            throw $this->createNotFoundException();
        }

        $document = null;

        foreach ($order->getDocuments()->getIterator() as $d) {
            /** @var OrderDocument $d */
            if ($d->getName() == $name) {
                $document = $d;
            }
        }

        if (!$document) {
            throw $this->createNotFoundException();
        }

        $fp = fopen($document->getPath(), "rb");
        $str = stream_get_contents($fp);
        fclose($fp);

        $headers = [];
        $headers['Content-Type'] = $document->getMimeType();

        if ($download) {
            $headers['Content-Disposition'] = 'attachment; filename="'.$document->getOriginalName().'"';
            $headers['Content-Length'] = filesize($document->getPath());
        }

        $response = new Response($str, 200, $headers);

        return $response;
    }

    /**
     * @param Order $entity
     * @param Package $package
     * @param $name
     * @param Request $request
     * @Route("/document/{id}/edit/{packageId}/{name}", name="order_document_edit", options={"expose"=true}, defaults={"download" = 0})
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("order", class="MBHPackageBundle:Order")
     * @ParamConverter("package", class="MBHPackageBundle:Package", options={"id" = "packageId"})
     * @Template()
     * @return RedirectResponse|null
     */
    public function editAction(Order $entity, Package $package, $name, Request $request)
    {
        $orderDocument = $entity->getDocument($name);
        $permissions = $this->container->get('mbh.package.permissions');
        
        if (!$orderDocument || !$permissions->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $touristIds = $this->get('mbh.helper')->toIds($package->getTourists());

        if ($mainTourist = $package->getMainTourist()) {
            $touristIds[] = $mainTourist->getId();
        }
        $docTypes = $this->container->getParameter('mbh.order.document.types');

        $form = $this->createForm(new OrderDocumentType(), $orderDocument, [
            'documentTypes' => $docTypes,
            'touristIds' => $touristIds,
            'scenario' => OrderDocumentType::SCENARIO_EDIT,
            'document' => $orderDocument
        ]);

        if ($request->isMethod("PUT")) {
            $oldOrderDocument = clone($orderDocument);
            $form->submit($request);

            if ($form->isValid()) {
                if (!$orderDocument->isUploaded()) {
                    $orderDocument->upload();
                    $oldOrderDocument->deleteFile();
                }
                $this->dm->persist($orderDocument);
                $this->dm->flush();

                return $this->redirect($this->generateUrl("order_documents", ['id' => $entity->getId(), 'packageId' => $package->getId()]));
            }
        }

        return [
            'entity' => $entity,
            'package' => $package,
            'document' => $orderDocument,
            'form' => $form->createView(),
            'logs' => $this->logs($package),
        ];
    }




    /**
     * Return pdf doc
     *
     * @Route("/{id}/pdf/{type}", name="package_pdf", requirements={
     *      "type" : "confirmation|confirmation_en|registration_card|fms_form_5|evidence|form_1_g|receipt|act|xls_notice"
     * })
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     */
    public function actPdfAction(Package $entity, $type, Request $request)
    {
        if (!$this->container->get('mbh.package.permissions')->checkHotel($entity)) {
            throw $this->createNotFoundException();
        }

        $generatorFactory = $this->get('mbh.package.document_factory');
        if(!in_array($type, $generatorFactory->getAvailableTypes())) {
            throw $this->createNotFoundException();
        }

        $formData = [];
        if($request->isMethod(Request::METHOD_POST) && $generatorFactory->hasForm($type)) {
            $options = [];
            if($type == XlsGeneratorFactory::TYPE_NOTICE) {
                $options['tourists'] = $entity->getTourists();
                $options['tourists'][] = $entity->getMainTourist();
            }
            $form = $generatorFactory->createFormByType($type, $options);
            $form->submit($request);
            if ($form->isValid()) {
                $formData = $form->getData();
            }/*else {
                throw $this->createNotFoundException();
            }*/
        }
        $formData['package'] = $entity;

        $documentGenerator = $generatorFactory->createGeneratorByType($type);
        $response = $documentGenerator->generateResponse($formData);

        return $response;
    }

    /**
     * @Route("/document/{id}/modal_form/{type}", name="document_modal_form", options={"expose"=true})
     * @ParamConverter("entity", class="MBHPackageBundle:Package")
     * @Template()
     */
    public function documentModalFormAction(Package $entity, $type)
    {
        $generatorFactory = $this->get('mbh.package.document_factory');
        if(!$generatorFactory->hasForm($type)) {
            throw $this->createNotFoundException();
        }

        $options = [];
        if($type == XlsGeneratorFactory::TYPE_NOTICE) {
            $options['tourists'] = $entity->getTourists();
            $options['tourists'][] = $entity->getMainTourist();
        }
        $form = $generatorFactory->createFormByType($type, $options);

        $html = $this->renderView('MBHPackageBundle:Documents:documentModalForm.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'entity' => $entity
        ]);

        return new JsonResponse([
            'html' => $html,
            'name' => $this->get('translator')->trans('templateDocument.type.'. $type)
        ]);
    }

    /**
     * @Route("/stamp/{id}.jpg", name="stamp")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @return Response
     * @ParamConverter(class="\MBH\Bundle\PackageBundle\Document\Organization")
     */
    public function stampAction(Organization $entity)
    {
        if (!$entity->getStamp()) {
            throw $this->createNotFoundException();
        }

        $fp = fopen($entity->getStamp()->getPathname(), "rb");
        $str = stream_get_contents($fp);
        fclose($fp);

        /*$binary = $this->get('liip_imagine.data.manager')->find('stamp',
            '/orderDocuments/5554599b7d3d6494118b4567'//$entity->getStamp()->getPathname()
        );
        $str = $binary->getContent();*/

        $response = new Response($str, 200);
        $response->headers->set('Content-Type', $entity->getStamp()->getMimeType());

        return $response;
    }
}