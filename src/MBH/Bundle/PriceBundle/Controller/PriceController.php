<?php

namespace MBH\Bundle\PriceBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\PriceBundle\Document\RoomPrice;
use MBH\Bundle\PriceBundle\Document\RoomQuota;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PriceBundle\Form\RoomQuotaType;
use MBH\Bundle\PriceBundle\Form\RoomPriceType;

/**
 * @Route("tariff")
 */
class PriceController extends Controller implements CheckHotelControllerInterface
{
    /**
     * @Route("/cache", name="cache")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function cacheAction(Request $request)
    {
        empty($request->get('route')) ? $route = '_welcome' : $route = $request->get('route');
        
        $now = new \DateTime();
        $now->modify('+ 1 minute');
        $request->getSession()->getFlashBag()
            ->set('success', 'Пересчет цен начнется в ' . $now->format('H:i'));
        
        $this->get('mbh.room.cache.generator')->generateInBackground(true);
        
        return $this->redirect($this->generateUrl($route));
    }
    
    /**
     * @Route("/{id}/room/quota", name="room_quota")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function roomQuotaAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPriceBundle:Tariff')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
                new RoomQuotaType(), [], ['entity' => $entity]
        );

        return array(
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/room/quota/create", name="room_quota_create")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHPriceBundle:Price:roomQuota.html.twig")
     */
    public function roomQuotaCreateAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPriceBundle:Tariff')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
                new RoomQuotaType(), [], ['entity' => $entity]
        );
        $form->bind($request);

        if ($form->isValid()) {
            
            $entity->removeAllRoomQuotas();
            
            foreach ($form->getData() as $roomTypeId => $value) {
                if ($value === null) {
                    continue;
                }
                $roomType = $dm->getRepository('MBHHotelBundle:RoomType')->find($roomTypeId);
                
                if (!$roomType) {
                    continue;
                }
                
                ((int) $value > $roomType->getRooms()->count()) ? $number = $roomType->getRooms()->count() : $number = (int) $value;
                
                $quota = new RoomQuota();
                $quota->setRoomType($roomType)->setNumber($number);
                $entity->addRoomQuota($quota);
            }

            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Квоты успешно сохранены.')
            ;
            
            $this->get('mbh.room.cache.generator')->generateInBackground();
            
            if ($this->getRequest()->get('save') !== null) {

                return $this->redirect($this->generateUrl('room_quota', array_merge(['id' => $id])));
            }

            return $this->redirect($this->generateUrl('tariff'));
        }

        return array(
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
        );
    }
    
    /**
     * @Route("/{id}/room/price", name="room_price")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template()
     */
    public function roomPriceAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPriceBundle:Tariff')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
                new RoomPriceType(), [], ['entity' => $entity]
        );

        return array(
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/room/price/create", name="room_price_create")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN_HOTEL')")
     * @Template("MBHPriceBundle:Price:roomPrice.html.twig")
     */
    public function roomPriceCreateAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository('MBHPriceBundle:Tariff')->find($id);

        if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(
                new RoomPriceType(), [], ['entity' => $entity]
        );
        $form->bind($request);

        if ($form->isValid()) {
            
            $entity->removeAllRoomPrices();
            
            foreach (RoomPriceType::parseData($form->getData()) as $roomTypeId =>  $price) {
                
                $roomType = $dm->getRepository('MBHHotelBundle:RoomType')->find($roomTypeId);
                
                if (!$roomType) {
                    continue;
                }
                $roomPrice = new RoomPrice();
                $roomPrice->setRoomType($roomType)
                          ->setPrice((isset($price['price'])) ? $price['price'] : null)
                          ->setAdditionalAdultPrice((isset($price['additionalAdultPrice'])) ? $price['additionalAdultPrice'] : null)
                          ->setAdditionalChildPrice((isset($price['additionalChildPrice'])) ? $price['additionalChildPrice'] : null)
                ;
                $entity->addRoomPrice($roomPrice);
            }
            
            $dm->persist($entity);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                    ->set('success', 'Цены успешно сохранены.')
            ;
            
            $this->get('mbh.room.cache.generator')->generateInBackground();
            
            if ($this->getRequest()->get('save') !== null) {

                return $this->redirect($this->generateUrl('room_price', array_merge(['id' => $id])));
            }

            return $this->redirect($this->generateUrl('tariff'));
        }

        return array(
            'entity' => $entity,
            'logs' => $this->logs($entity),
            'form' => $form->createView(),
        );
    }

}
