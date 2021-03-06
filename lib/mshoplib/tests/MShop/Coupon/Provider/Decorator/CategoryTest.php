<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 */


namespace Aimeos\MShop\Coupon\Provider\Decorator;


class CategoryTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;
	private $orderBase;
	private $couponItem;


	protected function setUp()
	{
		$orderProducts = [];
		$this->context = \TestHelperMShop::getContext();
		$this->couponItem = \Aimeos\MShop\Coupon\Manager\Factory::createManager( $this->context )->createItem();

		$provider = new \Aimeos\MShop\Coupon\Provider\Example( $this->context, $this->couponItem, 'abcd' );
		$this->object = new \Aimeos\MShop\Coupon\Provider\Decorator\Category( $provider, $this->context, $this->couponItem, 'abcd');
		$this->object->setObject( $this->object );

		$priceManager = \Aimeos\MShop\Factory::createManager( $this->context, 'price' );
		$productManager = \Aimeos\MShop\Factory::createManager( $this->context, 'product' );
		$product = $productManager->findItem( 'CNC' );

		$orderProductManager = \Aimeos\MShop\Factory::createManager( $this->context, 'order/base/product' );
		$orderProduct = $orderProductManager->createItem();
		$orderProduct->copyFrom( $product );

		$this->orderBase = new \Aimeos\MShop\Order\Item\Base\Standard( $priceManager->createItem(), $this->context->getLocale() );
		$this->orderBase->addProduct( $orderProduct );
	}


	protected function tearDown()
	{
		unset( $this->object );
		unset( $this->orderBase );
		unset( $this->couponItem );
	}


	public function testGetConfigBE()
	{
		$result = $this->object->getConfigBE();

		$this->assertArrayHasKey( 'category.catid', $result );
	}


	public function testCheckConfigBE()
	{
		$attributes = ['category.catid' => '123'];
		$result = $this->object->checkConfigBE( $attributes );

		$this->assertEquals( 1, count( $result ) );
		$this->assertInternalType( 'null', $result['category.catid'] );
	}


	public function testCheckConfigBEFailure()
	{
		$result = $this->object->checkConfigBE( [] );

		$this->assertEquals( 1, count( $result ) );
		$this->assertInternalType( 'string', $result['category.catid'] );
	}


	public function testIsAvailable()
	{
		$this->assertTrue( $this->object->isAvailable( $this->orderBase ) );
	}


	public function testIsAvailableWithProduct()
	{
		$this->couponItem->setConfig( array( 'category.catid' => $this->getCategory()->getId() ) );

		$this->assertTrue( $this->object->isAvailable( $this->orderBase ) );
	}


	public function testIsAvailableWithoutProduct()
	{
		$this->couponItem->setConfig( array( 'category.catid' => $this->getCategory( 'tea' )->getId() ) );

		$this->assertFalse( $this->object->isAvailable( $this->orderBase ) );
	}


	protected function getCategory( $code = 'cafe' )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->context, 'catalog' );
		return $manager->findItem( $code );
	}
}
