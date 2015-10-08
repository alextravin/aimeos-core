<?php

namespace Aimeos\Controller\ExtJS\Product\Tag;


/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2011
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 */
class StandardTest extends \PHPUnit_Framework_TestCase
{
	private $object;


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp()
	{
		$this->object = new \Aimeos\Controller\ExtJS\Product\Tag\Standard( \TestHelper::getContext() );
	}


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 */
	protected function tearDown()
	{
		$this->object = null;
	}


	public function testSearchItems()
	{
		$params = (object) array(
			'site' => 'unittest',
			'condition' => (object) array( '&&' => array( 0 => (object) array( '==' => (object) array( 'product.tag.languageid' => 'de' ) ) ) ),
			'sort' => 'product.tag.label',
			'dir' => 'ASC',
			'start' => 0,
			'limit' => 1,
		);

		$result = $this->object->searchItems( $params );

		$this->assertEquals( 1, count( $result['items'] ) );
		$this->assertEquals( 6, $result['total'] );
		$this->assertEquals( 'Cappuccino', $result['items'][0]->{'product.tag.label'} );
	}


	public function testSaveDeleteItem()
	{
		$searchParams = (object) array(
			'site' => 'unittest',
			'condition' => (object) array( '&&' => array( 0 => (object) array( '==' => (object) array( 'product.tag.type.code' => 'taste' ) ) ) )
		);

		$typeCtrl = new \Aimeos\Controller\ExtJS\Product\Tag\Type\Standard( \TestHelper::getContext() );
		$types = $typeCtrl->searchItems( $searchParams );
		$this->assertEquals( 1, count( $types['items'] ) );


		$saveParams = (object) array(
			'site' => 'unittest',
			'items' =>  (object) array(
				'product.tag.typeid' => $types['items'][0]->{'product.tag.type.id'},
				'product.tag.languageid' => 'de',
				'product.tag.label' => 'unittest',
			),
		);

		$searchParams = (object) array(
			'site' => 'unittest',
			'condition' => (object) array( '&&' => array( 0 => (object) array( '==' => (object) array( 'product.tag.label' => 'unittest' ) ) ) )
		);

		$saved = $this->object->saveItems( $saveParams );
		$searched = $this->object->searchItems( $searchParams );

		$deleteParams = (object) array( 'site' => 'unittest', 'items' => $saved['items']->{'product.tag.id'} );
		$this->object->deleteItems( $deleteParams );
		$result = $this->object->searchItems( $searchParams );

		$this->assertInternalType( 'object', $saved['items'] );
		$this->assertNotNull( $saved['items']->{'product.tag.id'} );
		$this->assertEquals( $saved['items']->{'product.tag.id'}, $searched['items'][0]->{'product.tag.id'} );
		$this->assertEquals( $saved['items']->{'product.tag.typeid'}, $searched['items'][0]->{'product.tag.typeid'} );
		$this->assertEquals( $saved['items']->{'product.tag.languageid'}, $searched['items'][0]->{'product.tag.languageid'} );
		$this->assertEquals( $saved['items']->{'product.tag.label'}, $searched['items'][0]->{'product.tag.label'} );
		$this->assertEquals( 1, count( $searched['items'] ) );
		$this->assertEquals( 0, count( $result['items'] ) );
	}
}