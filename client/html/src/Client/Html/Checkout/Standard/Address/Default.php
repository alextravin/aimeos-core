<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2013
 * @license LGPLv3, http://www.arcavias.com/en/license
 * @package Client
 * @subpackage Html
 */


// Strings for translation
_('address');


/**
 * Default implementation of checkout address HTML client.
 *
 * @package Client
 * @subpackage Html
 */
class Client_Html_Checkout_Standard_Address_Default
	extends Client_Html_Abstract
{
	/** client/html/checkout/standard/address/default/subparts
	 * List of HTML sub-clients rendered within the checkout standard address section
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
	private $_subPartPath = 'client/html/checkout/standard/address/default/subparts';

	/** client/html/checkout/standard/address/billing/name
	 * Name of the billing part used by the checkout standard address client implementation
	 *
	 * Use "Myname" if your class is named "Client_Checkout_Standard_Address_Billing_Myname".
	 * The name is case-sensitive and you should avoid camel case names like "MyName".
	 *
	 * @param string Last part of the client class name
	 * @since 2014.03
	 * @category Developer
	 */

	/** client/html/checkout/standard/address/delivery/name
	 * Name of the delivery part used by the checkout standard address client implementation
	 *
	 * Use "Myname" if your class is named "Client_Checkout_Standard_Address_Delivery_Myname".
	 * The name is case-sensitive and you should avoid camel case names like "MyName".
	 *
	 * @param string Last part of the client class name
	 * @since 2014.03
	 * @category Developer
	 */
	private $_subPartNames = array( 'billing', 'delivery' );

	private $_cache;


	/**
	 * Returns the HTML code for insertion into the body.
	 *
	 * @return string HTML code
	 */
	public function getBody()
	{
		$view = $this->getView();

		if( $view->get( 'standardStepActive', 'address' ) != 'address' ) {
			return '';
		}

		$view = $this->_setViewParams( $view );

		$html = '';
		foreach( $this->_getSubClients( $this->_subPartPath, $this->_subPartNames ) as $subclient ) {
			$html .= $subclient->setView( $view )->getBody();
		}
		$view->addressBody = $html;

		/** client/html/checkout/standard/address/default/template-body
		 * Relative path to the HTML body template of the checkout standard address client.
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
		 * @see client/html/checkout/standard/address/default/template-header
		 */
		$tplconf = 'client/html/checkout/standard/address/default/template-body';
		$default = 'checkout/standard/address-body-default.html';

		return $view->render( $this->_getTemplate( $tplconf, $default ) );
	}


	/**
	 * Returns the HTML string for insertion into the header.
	 *
	 * @return string String including HTML tags for the header
	 */
	public function getHeader()
	{
		$view = $this->getView();

		if( $view->get( 'standardStepActive', 'address' ) != 'address' ) {
			return '';
		}

		$view = $this->_setViewParams( $view );

		$html = '';
		foreach( $this->_getSubClients( $this->_subPartPath, $this->_subPartNames ) as $subclient ) {
			$html .= $subclient->setView( $view )->getHeader();
		}
		$view->addressHeader = $html;

		/** client/html/checkout/standard/address/default/template-header
		 * Relative path to the HTML header template of the checkout standard address client.
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
		 * @see client/html/checkout/standard/address/default/template-body
		 */
		$tplconf = 'client/html/checkout/standard/address/default/template-header';
		$default = 'checkout/standard/address-header-default.html';

		return $view->render( $this->_getTemplate( $tplconf, $default ) );
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
		return $this->_createSubClient( 'checkout/standard/address/' . $type, $name );
	}


	/**
	 * Tests if the output of is cachable.
	 *
	 * @param integer $what Header or body constant from Client_HTML_Abstract
	 * @return boolean True if the output can be cached, false if not
	 */
	public function isCachable( $what )
	{
		return false;
	}


	/**
	 * Processes the input, e.g. store given values.
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables.
	 */
	public function process()
	{
		$view = $this->getView();

		try
		{
			$this->_process( $this->_subPartPath, $this->_subPartNames );

			$basketCntl = Controller_Frontend_Factory::createController( $this->_getContext(), 'basket' );

			// Test if addresses are available
			$addresses = $basketCntl->get()->getAddresses();
			if( !isset( $view->standardStepActive ) && count( $addresses ) === 0 )
			{
				$view->standardStepActive = 'address';
				return false;
			}
		}
		catch( Exception $e )
		{
			$this->getView()->standardStepActive = 'address';
			throw $e;
		}

	}


	/**
	 * Sets the necessary parameter values in the view.
	 *
	 * @param MW_View_Interface $view The view object which generates the HTML output
	 * @return MW_View_Interface Modified view object
	 */
	protected function _setViewParams( MW_View_Interface $view )
	{
		if( !isset( $this->_cache ) )
		{
			$context = $this->_getContext();


			$customerManager = MShop_Customer_Manager_Factory::createManager( $context );

			$search = $customerManager->createSearch( true );
			$expr = array(
				$search->compare( '==', 'customer.code', $context->getEditor() ),
				$search->getConditions(),
			);
			$search->setConditions( $search->combine( '&&', $expr ) );

			$items = $customerManager->searchItems( $search );

			if( ( $item = reset( $items ) ) !== false )
			{
				$view->addressCustomerItem = $item;

				$customerAddressManager = $customerManager->getSubManager( 'address' );

				$search = $customerAddressManager->createSearch();
				$search->setConditions( $search->compare( '==', 'customer.address.refid', $item->getId() ) );

				$view->addressCustomerAddressItems = $customerAddressManager->searchItems( $search );
			}


			$localeManager = MShop_Locale_Manager_Factory::createManager( $context );
			$locales = $localeManager->searchItems( $localeManager->createSearch( true ) );

			$languages = array();
			foreach( $locales as $locale ) {
				$languages[] = $locale->getLanguageId();
			}

			$view->addressLanguages = $languages;

			/** client/html/common/address/countries
			 * List of available countries that that users can select from in the front-end
			 *
			 * This configration option is used whenever a list of countries is
			 * shown in the front-end users can select from. It's used e.g.
			 * if the customer should select the country he is living in the
			 * checkout process. In case that the list is empty, no country
			 * selection is shown. Each list entry must be a two letter ISO
			 * country code that is then translated into its name.
			 *
			 * @param array List of two letter ISO country codes
			 * @since 2014.03
			 * @category User
			 * @category Developer
			 */
			$view->addressCountries = $view->config( 'client/html/common/address/countries', array() );


			$this->_cache = $view;
		}

		return $this->_cache;
	}
}