<?php
/**
 * Script to initial insertion of Ware Categories (tune $cats to set example titles).
 * 
 * Usage: issue the following
 * bin/console doctrine:mongodb:fixtures:load --append --fixtures=/var/www/mbh/src/MBH/Bundle/WarehouseBundle/DataFixtures/MongoDB/
 * 
 * Jvb 15.04.2016
 * 
 */

namespace MBH\Bundle\WarehouseBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\WarehouseBundle\Document\WareCategory;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WarehouseData implements FixtureInterface, ContainerAwareInterface
{
	private $cats = [
		'Бытовая химия',
		'Питание',
		'Бельё',
	];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
		$existingCats = $manager->getRepository('MBHWarehouseBundle:WareCategory')->findAll();
		
		// skip existing categories
		foreach ($this->cats as $cat) {
			if ($this->checkExistance($existingCats, $cat)) {
				continue;
			}
			
			$wcat = new WareCategory();
			
			$wcat->setTitle($cat);
			
			$manager->persist($wcat);
		}
				
		$manager->flush();
    }
	
	/**
	 * Aux: iterate through all the categories and check if one we are going to insert exists.
	 * 
	 * @param WareCategory $existingCats
	 * @param string $cat
	 * @return boolean
	 */
	function checkExistance($existingCats, $cat) {
		foreach ($existingCats as $v) {
			if ($v->getTitle() == $cat) {
				return true;
			}
		}
		
		return false;
	}
	
}
