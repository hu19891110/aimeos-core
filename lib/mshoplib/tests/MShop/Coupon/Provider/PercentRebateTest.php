<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2012
 * @license LGPLv3, http://www.arcavias.com/en/license
 */


/**
 * Test class for MShop_Coupon_Provider_PercentRebate.
 */
class MShop_Coupon_Provider_PercentRebateTest extends MW_Unittest_Testcase
{
	private $_object;
	private $_orderBase;


	/**
	 * Runs the test methods of this class.
	 *
	 * @access public
	 * @static
	 */
	public static function main()
	{
		require_once 'PHPUnit/TextUI/TestRunner.php';

		$suite  = new PHPUnit_Framework_TestSuite('MShop_Coupon_Provider_PercentRebateTest');
		$result = PHPUnit_TextUI_TestRunner::run($suite);
	}


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp()
	{
		$context = TestHelper::getContext();

		$couponManager = MShop_Coupon_Manager_Factory::createManager( $context );
		$search = $couponManager->createSearch();
		$search->setConditions( $search->compare( '==', 'coupon.code.code', '90AB') );
		$results = $couponManager->searchItems( $search );

		if( ( $couponItem = reset( $results ) ) === false ) {
			throw new Exception( 'No coupon item found' );
		}

		$this->_object = new MShop_Coupon_Provider_PercentRebate( $context, $couponItem, '90AB' );


		$orderManager = MShop_Order_Manager_Factory::createManager( $context );
		$orderBaseManager = $orderManager->getSubManager('base');
		$orderProductManager = $orderBaseManager->getSubManager( 'product' );

		$productManager = MShop_Product_Manager_Factory::createManager( $context );
		$search = $productManager->createSearch();
		$search->setConditions( $search->compare( '==', 'product.code', array( 'CNE' ) ) );
		$products = $productManager->searchItems( $search, array('price') );

		$priceIds = $priceMap = array();

		foreach( $products as $product )
		{
			foreach ( $product->getListItems( 'price' ) AS $listItem )
			{
				$priceIds[] = $listItem->getRefId();
				$priceMap[ $listItem->getRefId() ] = $product->getCode();
			}

			$orderProduct = $orderProductManager->createItem();
			$orderProduct->setName( $product->getName() );
			$orderProduct->setProductCode( $product->getCode() );
			$orderProduct->setQuantity( 1 );

			$this->orderProducts[ $product->getCode() ] = $orderProduct;
		}

		$priceManager = MShop_Price_Manager_Factory::createManager( $context );
		$search = $priceManager->createSearch();
		$expr[] = $search->compare( '==', 'price.id', $priceIds );
		$expr[] = $search->compare( '==', 'price.quantity', 1 );
		$search->setConditions( $search->combine( '&&', $expr ) );

		foreach( $priceManager->searchItems( $search ) as $priceItem )
		{
			$productCode = $priceMap[ $priceItem->getId() ];
			$this->orderProducts[ $productCode ]->setPrice( $priceItem );
		}


		// Don't create order base item by createItem() as this would already register the plugins
		$this->_orderBase = new MShop_Order_Item_Base_Default( $priceManager->createItem(), $context->getLocale() );
	}


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 */
	protected function tearDown()
	{
		unset( $this->_object );
		unset( $this->_orderBase );
	}


	public function testAddCoupon()
	{
		$this->_orderBase->addProduct( $this->orderProducts['CNE'] );
		$this->_object->addCoupon( $this->_orderBase );

		$coupons = $this->_orderBase->getCoupons();
		$products = $this->_orderBase->getProducts();

		if( ( $product = reset( $coupons['90AB'] ) ) === false ) {
			throw new Exception( 'No coupon available' );
		}

		$this->assertEquals( 2, count( $products ) );
		$this->assertEquals( 1, count( $coupons['90AB'] ) );
		$this->assertEquals( '-1.80', $product->getPrice()->getValue() );
		$this->assertEquals( '1.80', $product->getPrice()->getRebate() );
		$this->assertEquals( 'unitSupplier', $product->getSupplierCode() );
		$this->assertEquals( 'U:MD', $product->getProductCode() );
		$this->assertNotEquals( '', $product->getProductId() );
		$this->assertEquals( '', $product->getMediaUrl() );
		$this->assertEquals( 'Geldwerter Nachlass', $product->getName() );
	}


	public function testDeleteCoupon()
	{
		$this->_orderBase->addProduct( $this->orderProducts['CNE'] );

		$this->_object->addCoupon( $this->_orderBase );
		$this->_object->deleteCoupon($this->_orderBase);

		$products = $this->_orderBase->getProducts();
		$coupons = $this->_orderBase->getCoupons();

		$this->assertEquals( 1, count( $products ) );
		$this->assertArrayNotHasKey( '90AB', $coupons );
	}


	public function testAddCouponInvalidConfig()
	{
		$outer = null;

		$context = TestHelper::getContext();
		$this->manager = MShop_Coupon_Manager_Factory::createManager( TestHelper::getContext() );
		$couponItem=$this->manager->createItem();

		$this->manager = new MShop_Coupon_Provider_PercentRebate( $context, $couponItem, '5678', $outer );

		$this->setExpectedException('MShop_Coupon_Exception');
		$this->manager->addCoupon($this->_orderBase);
	}

	public function testIsAvailable()
	{
		$this->assertTrue( $this->_object->isAvailable( $this->_orderBase ) );
	}

}