<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2013
 * @license LGPLv3, http://www.arcavias.com/en/license
 * @package Client
 * @subpackage Html
 */


/**
 * Default implementation of catalog stage section HTML clients.
 *
 * @package Client
 * @subpackage Html
 */
class Client_Html_Catalog_Stage_Default
	extends Client_Html_Abstract
{
	/** client/html/catalog/stage/default/subparts
	 * List of HTML sub-clients rendered within the catalog stage section
	 *
	 * The output of the frontend is composed of the code generated by the HTML
	 * clients. Each HTML client can consist of serveral (or none) sub-clients
	 * that are responsible for rendering certain sub-parts of the output. The
	 * sub-clients can contain HTML clients themselves and therefore a
	 * hierarchical tree of HTML clients is composed. Each HTML client creates
	 * the output that is placed inside the container of its parent.
	 *
	 * At first, always the HTML code generated by the parent is printed, then
	 * the HTML code of its sub-clients. The order of the HTML sub-clients
	 * determines the order of the output of these sub-clients inside the parent
	 * container. If the configured list of clients is
	 *
	 *  array( "subclient1", "subclient2" )
	 *
	 * you can easily change the order of the output by reordering the subparts:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1", "subclient2" )
	 *
	 * You can also remove one or more parts if they shouldn't be rendered:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1" )
	 *
	 * As the clients only generates structural HTML, the layout defined via CSS
	 * should support adding, removing or reordering content by a fluid like
	 * design.
	 *
	 * @param array List of sub-client names
	 * @since 2014.03
	 * @category Developer
	 */
	private $_subPartPath = 'client/html/catalog/stage/default/subparts';

	/** client/html/catalog/stage/image/name
	 * Name of the image part used by the catalog stage client implementation
	 *
	 * Use "Myname" if your class is named "Client_Html_Catalog_Stage_Image_Myname".
	 * The name is case-sensitive and you should avoid camel case names like "MyName".
	 *
	 * @param string Last part of the client class name
	 * @since 2014.03
	 * @category Developer
	 */

	/** client/html/catalog/stage/breadcrumb/name
	 * Name of the breadcrumb part used by the catalog stage client implementation
	 *
	 * Use "Myname" if your class is named "Client_Html_Catalog_Stage_Breadcrumb_Myname".
	 * The name is case-sensitive and you should avoid camel case names like "MyName".
	 *
	 * @param string Last part of the client class name
	 * @since 2014.03
	 * @category Developer
	 */

	/** client/html/catalog/stage/navigator/name
	 * Name of the navigator part used by the catalog stage client implementation
	 *
	 * Use "Myname" if your class is named "Client_Html_Catalog_Stage_Breadcrumb_Myname".
	 * The name is case-sensitive and you should avoid camel case names like "MyName".
	 *
	 * @param string Last part of the client class name
	 * @since 2014.09
	 * @category Developer
	 */
	private $_subPartNames = array( 'image', 'breadcrumb', 'navigator' );

	private $_tags = array();
	private $_expire;
	private $_params;
	private $_cache;
	private $_view;


	/**
	 * Returns the HTML code for insertion into the body.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return string HTML code
	 */
	public function getBody( $uid = '', array &$tags = array(), &$expire = null )
	{
		if( ( $html = $this->_getCached( 'body', $uid ) ) === null )
		{
			$context = $this->_getContext();
			$view = $this->getView();

			try
			{
				$view = $this->_setViewParams( $view, $tags, $expire );

				$output = '';
				foreach( $this->_getSubClients() as $subclient ) {
					$output .= $subclient->setView( $view )->getBody( $uid, $tags, $expire );
				}
				$view->stageBody = $output;
			}
			catch( Client_Html_Exception $e )
			{
				$error = array( $context->getI18n()->dt( 'client/html', $e->getMessage() ) );
				$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
			}
			catch( Controller_Frontend_Exception $e )
			{
				$error = array( $context->getI18n()->dt( 'controller/frontend', $e->getMessage() ) );
				$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
			}
			catch( MShop_Exception $e )
			{
				$error = array( $context->getI18n()->dt( 'mshop', $e->getMessage() ) );
				$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
			}
			catch( Exception $e )
			{
				$context->getLogger()->log( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );

				$view = $this->getView();
				$error = array( $context->getI18n()->dt( 'client/html', 'A non-recoverable error occured' ) );
				$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
			}

			/** client/html/catalog/stage/default/template-body
			 * Relative path to the HTML body template of the catalog stage client.
			 *
			 * The template file contains the HTML code and processing instructions
			 * to generate the result shown in the body of the frontend. The
			 * configuration string is the path to the template file relative
			 * to the layouts directory (usually in client/html/layouts).
			 *
			 * You can overwrite the template file configuration in extensions and
			 * provide alternative templates. These alternative templates should be
			 * named like the default one but with the string "default" replaced by
			 * an unique name. You may use the name of your project for this. If
			 * you've implemented an alternative client class as well, "default"
			 * should be replaced by the name of the new class.
			 *
			 * @param string Relative path to the template creating code for the HTML page body
			 * @since 2014.03
			 * @category Developer
			 * @see client/html/catalog/stage/default/template-header
			 */
			$tplconf = 'client/html/catalog/stage/default/template-body';
			$default = 'catalog/stage/body-default.html';

			$html = $view->render( $this->_getTemplate( $tplconf, $default ) );

			$this->_setCached( 'body', $uid, $html, $tags, $expire );
		}
		else
		{
			$html = $this->modifyBody( $html, $uid );
		}

		return $html;
	}


	/**
	 * Returns the HTML string for insertion into the header.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return string String including HTML tags for the header
	 */
	public function getHeader( $uid = '', array &$tags = array(), &$expire = null )
	{
		if( ( $html = $this->_getCached( 'header', $uid ) ) === null )
		{
			$context = $this->_getContext();
			$view = $this->getView();

			try
			{
				$view = $this->_setViewParams( $view, $tags, $expire );

				$output = '';
				foreach( $this->_getSubClients() as $subclient ) {
					$output .= $subclient->setView( $view )->getHeader( $uid, $tags, $expire );
				}
				$view->stageHeader = $html;

				/** client/html/catalog/stage/default/template-header
				 * Relative path to the HTML header template of the catalog stage client.
				 *
				 * The template file contains the HTML code and processing instructions
				 * to generate the HTML code that is inserted into the HTML page header
				 * of the rendered page in the frontend. The configuration string is the
				 * path to the template file relative to the layouts directory (usually
				 * in client/html/layouts).
				 *
				 * You can overwrite the template file configuration in extensions and
				 * provide alternative templates. These alternative templates should be
				 * named like the default one but with the string "default" replaced by
				 * an unique name. You may use the name of your project for this. If
				 * you've implemented an alternative client class as well, "default"
				 * should be replaced by the name of the new class.
				 *
				 * @param string Relative path to the template creating code for the HTML page head
				 * @since 2014.03
				 * @category Developer
				 * @see client/html/catalog/stage/default/template-body
				 */
				$tplconf = 'client/html/catalog/stage/default/template-header';
				$default = 'catalog/stage/header-default.html';

				$html = $view->render( $this->_getTemplate( $tplconf, $default ) );

				$this->_setCached( 'header', $uid, $html, $tags, $expire );
			}
			catch( Exception $e )
			{
				$context->getLogger()->log( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
			}
		}
		else
		{
			$html = $this->modifyHeader( $html, $uid );
		}

		return $html;
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return Client_Html_Interface Sub-client object
	 */
	public function getSubClient( $type, $name = null )
	{
		return $this->_createSubClient( 'catalog/stage/' . $type, $name );
	}


	/**
	 * Processes the input, e.g. store given values.
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables.
	 */
	public function process()
	{
		$context = $this->_getContext();
		$view = $this->getView();

		try
		{
			$view->stageParams = $this->_getParamStage( $view );

			parent::process();
		}
		catch( Client_Html_Exception $e )
		{
			$error = array( $this->_getContext()->getI18n()->dt( 'client/html', $e->getMessage() ) );
			$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
		}
		catch( Controller_Frontend_Exception $e )
		{
			$error = array( $this->_getContext()->getI18n()->dt( 'controller/frontend', $e->getMessage() ) );
			$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
		}
		catch( MShop_Exception $e )
		{
			$error = array( $this->_getContext()->getI18n()->dt( 'mshop', $e->getMessage() ) );
			$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
		}
		catch( Exception $e )
		{
			$context->getLogger()->log( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );

			$error = array( $context->getI18n()->dt( 'client/html', 'A non-recoverable error occured' ) );
			$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
		}
	}


	/**
	 * Returns the cache entry for the given unique ID and type.
	 *
	 * @param string $type Type of the cache entry, i.e. "body" or "header"
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string Cached entry or empty string if not available
	 */
	protected function _getCached( $type, $uid )
	{
		if( !isset( $this->_cache ) )
		{
			$context = $this->_getContext();
			$config = $context->getConfig()->get( 'client/html/catalog/stage', array() );

			$keys = array(
				'body' => $this->_getParamHash( array( 'f' ), $uid . ':catalog:stage-body', $config ),
				'header' => $this->_getParamHash( array( 'f' ), $uid . ':catalog:stage-header', $config ),
			);

			$entries = $context->getCache()->getList( $keys );
			$this->_cache = array();

			foreach( $keys as $key => $hash ) {
				$this->_cache[$key] = ( array_key_exists( $hash, $entries ) ? $entries[$hash] : null );
			}
		}

		return ( array_key_exists( $type, $this->_cache ) ? $this->_cache[$type] : null );
	}


	/**
	 * Returns the cache entry for the given type and unique ID.
	 *
	 * @param string $type Type of the cache entry, i.e. "body" or "header"
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @param string $value Value string that should be stored for the given key
	 * @param array $tags List of tag strings that should be assoicated to the
	 * 	given value in the cache
	 * @param string|null $expire Date/time string in "YYYY-MM-DD HH:mm:ss"
	 * 	format when the cache entry expires
	 */
	protected function _setCached( $type, $uid, $value, array $tags, $expire )
	{
		$context = $this->_getContext();

		try
		{
			$config = $context->getConfig()->get( 'client/html/catalog/stage', array() );
			$key = $this->_getParamHash( array( 'f' ), $uid . ':catalog:stage-' . $type, $config );

			$context->getCache()->set( $key, $value, array_unique( $tags ), $expire );
		}
		catch( Exception $e )
		{
			$msg = sprintf( 'Unable to set cache entry: %1$s', $e->getMessage() );
			$context->getLogger()->log( $msg, MW_Logger_Abstract::NOTICE );
		}
	}


	/**
	 * Generates an unique hash from based on the input suitable to be used as part of the cache key
	 *
	 * @param array $prefixes List of prefixes the parameters must start with
	 * @param string $key Unique identifier if the content is placed more than once on the same page
	 * @param array $config Multi-dimensional array of configuration options used by the client and sub-clients
	 * @return string Unique hash
	 */
	protected function _getParamHash( array $prefixes = array( 'f', 'l', 'd' ), $key = '', array $config = array() )
	{
		$locale = $this->_getContext()->getLocale();
		$params = $this->_getClientParams( $this->getView()->param(), $prefixes );

		if( empty( $params ) )
		{
			$params = $this->_getContext()->getSession()->get( 'arcavias/catalog/list/params/last', '' );
			$params = ( ( $data = json_decode( $params, true ) ) !== null ? $data : array() );
		}

		ksort( $params );

		if( ( $pstr = json_encode( $params ) ) === false || ( $cstr = json_encode( $config ) ) === false ) {
			throw new Client_Html_Exception( 'Unable to encode parameters or configuration options' );
		}

		return md5( $key . $pstr . $cstr . $locale->getLanguageId() . $locale->getCurrencyId() );
	}


	/**
	 * Returns the required params for the stage clients, either from GET/POST or from the session.
	 *
	 * @param MW_View_Interface $view The view object which generates the HTML output
	 * @return array List of parameters
	 */
	protected function _getParamStage( MW_View_Interface $view )
	{
		if( !isset( $this->_params ) )
		{
			$params = $this->_getClientParams( $view->param(), array( 'f' ) );

			if( empty( $params ) )
			{
				$params = $this->_getContext()->getSession()->get( 'arcavias/catalog/list/params/last', '[]' );
				$params = ( ( $data = json_decode( $params, true ) ) !== null && is_array( $data ) ? $data : array() );
			}

			$this->_params = $params;
		}

		return $this->_params;
	}


	/**
	 * Returns the list of sub-client names configured for the client.
	 *
	 * @return array List of HTML client names
	 */
	protected function _getSubClientNames()
	{
		return $this->_getContext()->getConfig()->get( $this->_subPartPath, $this->_subPartNames );
	}


	/**
	 * Sets the necessary parameter values in the view.
	 *
	 * @param MW_View_Interface $view The view object which generates the HTML output
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return MW_View_Interface Modified view object
	 */
	protected function _setViewParams( MW_View_Interface $view, array &$tags = array(), &$expire = null )
	{
		if( !isset( $this->_view ) )
		{
			$params = $this->_getParamStage( $view );

			if( isset( $params['f-catalog-id'] ) )
			{
				$context = $this->_getContext();
				$config = $context->getConfig();
				$catalogManager = MShop_Factory::createManager( $context, 'catalog' );

				$default = array( 'attribute', 'media', 'text' );

				/** client/html/catalog/domains
				 * A list of domain names whose items should be available in the catalog view templates
				 *
				 * @see client/html/catalog/stage/domains
				 */
				$domains = $config->get( 'client/html/catalog/domains', $default );

				/** client/html/catalog/stage/default/domains
				 * A list of domain names whose items should be available in the catalog stage view template
				 *
				 * The templates rendering the catalog stage section use the texts and
				 * maybe images and attributes associated to the categories. You can
				 * configure your own list of domains (attribute, media, price, product,
				 * text, etc. are domains) whose items are fetched from the storage.
				 * Please keep in mind that the more domains you add to the configuration,
				 * the more time is required for fetching the content!
				 *
				 * This configuration option overwrites the "client/html/catalog/domains"
				 * option that allows to configure the domain names of the items fetched
				 * for all catalog related data.
				 *
				 * @param array List of domain names
				 * @since 2014.03
				 * @category Developer
				 * @see client/html/catalog/domains
				 * @see client/html/catalog/detail/domains
				 * @see client/html/catalog/list/domains
				 */
				$domains = $config->get( 'client/html/catalog/stage/default/domains', $domains );
				$stageCatPath = $catalogManager->getPath( $params['f-catalog-id'], $domains );

				if( ( $categoryItem = end( $stageCatPath ) ) !== false ) {
					$view->stageCurrentCatItem = $categoryItem;
				}

				$this->_addMetaItem( $stageCatPath, 'catalog', $this->_expire, $this->_tags );
				$this->_addMetaList( array_keys( $stageCatPath ), 'catalog', $this->_expire );

				$view->stageCatPath = $stageCatPath;
			}

			$view->stageParams = $params;

			$this->_view = $view;
		}

		$expire = $this->_expires( $this->_expire, $expire );
		$tags = array_merge( $tags, $this->_tags );

		return $this->_view;
	}
}
