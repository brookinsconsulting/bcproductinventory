<?php
//
// Definition of BCProductInventoryType class
//
// Created on: <09-21-2022 14:42:02 gb>
//
// COPYRIGHT NOTICE: Copyright (C) 1999-2022 Brookins Consulting
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301,  USA.
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

/*! \file bcproductinventorytype.php
*/

/*!
  \class BCProductInventoryType bcproductinventorytype.php
  \brief The class BCProductInventoryType handles adding maintaining the product inventory and other tasks like removing one item from the product_inventory_count for a newly completed order.
*/

class BCProductInventoryType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = 'bcproductinventory';

    /*!
     Constructor
    */
    function __construct()
    {
        parent::__construct( BCProductInventoryType::WORKFLOW_TYPE_STRING, ezpI18n::tr( 'kernel/workflow/event', "Remove From Inventory" ) );
        $this->setTriggerTypes( array( 'shop' => array( 'confirmorder' => array ( 'before' ) ) ) );
    }

    function execute( &$process, &$event )
    {
        // Fetch Workflow Settings
        $ini = eZINI::instance( 'workflow.ini' );

        // Setting for shipping calculation process debug
        // $debug = 'Enabled';//$ini->variable( "SimpleShippingWorkflow", "Debug" );

        // Process parameters
        $parameters = $process->attribute( 'parameter_list' );
        $orderID = $parameters['order_id'];

        // Fetch order
        $order = eZOrder::fetch( $orderID );

        // If order class was fetched
        if ( get_class( $order ) == 'eZOrder' )
        {
            // Fetch order products
            $productcollection = $order->productCollection();

            // Fetch order items
            $items = $productcollection->itemList();
            $orderItems = $order->attribute( 'order_items' );

	    foreach ( $items as $item )
            {
		$itemProductInventoryCountToRemove = $item->attribute('item_count');
		// $itemProductID = $item->ContentObjectID;
		
            	// Fetch object
            	$co = eZContentObject::fetch( $item->attribute( 'contentobject_id' ) );

            	// Fetch object datamap
            	$dm = $co->dataMap();

		// Are we removing item from ordered products inventory count, default is usualy yes here

		// Fetch product_inventory_count attribute and decrement it's count.
		$pica = $dm['product_inventory_count'];
		$pic = $pica->content();

		if ( $pic >= 1 )
		{
		    $newPic = $pic - 1;
		    $pica->fromString( $newPic );
		    $pica->store();
		    $co->store();
		}
		
		//var_dump($pica->Version);
		//var_dump($pica->attribute('content') );
            }
	}

        return eZWorkflowType::STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerEventType( BCProductInventoryType::WORKFLOW_TYPE_STRING, "BCProductInventoryType" );

?>