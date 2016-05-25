<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016
 */


namespace Aimeos\MW\Setup\Task;


/**
 * Creates all platform specific tables
 */
class TablesCreatePlatform extends \Aimeos\MW\Setup\Task\Base
{
	/**
	 * Returns the list of task names which this task depends on.
	 *
	 * @return string[] List of task names
	 */
	public function getPreDependencies()
	{
		return array( 'TablesCreateMAdmin', 'TablesCreateMShop' );
	}


	/**
	 * Returns the list of task names which depends on this task.
	 *
	 * @return array List of task names
	 */
	public function getPostDependencies()
	{
		return array();
	}


	/**
	 * Creates the platform specific schema
	 */
	public function migrate()
	{
		$this->msg( 'Creating platform specific schema', 0 );
		$this->status( '' );

		$ds = DIRECTORY_SEPARATOR;

		$this->setup( 'db-index', 'mysql', realpath( __DIR__ ) . $ds . 'default' . $ds . 'schema' . $ds . 'index-mysql.sql' );
		$this->setup( 'db-order', 'mysql', realpath( __DIR__ ) . $ds . 'default' . $ds . 'schema' . $ds . 'order-mysql.sql' );
		$this->setup( 'db-text', 'mysql', realpath( __DIR__ ) . $ds . 'default' . $ds . 'schema' . $ds . 'text-mysql.sql' );

		$this->setup( 'db-index', 'pgsql', realpath( __DIR__ ) . $ds . 'default' . $ds . 'schema' . $ds . 'index-pgsql.sql' );
		$this->setup( 'db-order', 'pgsql', realpath( __DIR__ ) . $ds . 'default' . $ds . 'schema' . $ds . 'order-pgsql.sql' );
		$this->setup( 'db-text', 'pgsql', realpath( __DIR__ ) . $ds . 'default' . $ds . 'schema' . $ds . 'text-pgsql.sql' );
	}


	/**
	 * Creates all required tables if they doesn't exist
	 */
	protected function setup( $rname, $adapter, $filepath )
	{
		$schema = $this->getSchema( $rname );

		if( $adapter !== $schema->getName() ) {
			return;
		}

		$this->msg( 'Using schema from ' . basename( $filepath ), 1 ); $this->status( '' );

		if( ( $content = file_get_contents( $filepath ) ) === false ) {
			throw new \Aimeos\MW\Setup\Exception( sprintf( 'Unable to get content from file "%1$s"', $filepath ) );
		}

		foreach( $this->getTableDefinitions( $content ) as $name => $sql )
		{
			$this->msg( sprintf( 'Checking table "%1$s": ', $name ), 2 );

			if( $schema->tableExists( $name ) !== true ) {
				$this->execute( $sql, $rname );
				$this->status( 'created' );
			} else {
				$this->status( 'OK' );
			}
		}

		foreach( $this->getIndexDefinitions( $content ) as $name => $sql )
		{
			$parts = explode( '.', $name );
			$this->msg( sprintf( 'Checking index "%1$s": ', $name ), 2 );

			if( $schema->indexExists( $parts[0], $parts[1] ) !== true ) {
				$this->execute( $sql, $rname );
				$this->status( 'created' );
			} else {
				$this->status( 'OK' );
			}
		}
	}
}